<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\ClasseSessao;
use App\Enums\StatusEvento;
use App\Enums\StatusFrequencia;
use App\Enums\TipoEvento;
use App\Enums\VisibilidadeEvento;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SalvarFrequenciaChancelariaRequest;
use App\Http\Requests\Admin\SalvarSessaoChancelariaRequest;
use App\Models\ChancelariaFrequencia;
use App\Models\Evento;
use App\Models\Irmao;
use App\Support\RegistradorDeAuditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class ChancelariaFrequenciaController extends Controller
{
    public function selecionarEvento(Request $request): View
    {
        $this->authorize('chancelaria.visualizar');

        // Anos que realmente possuem sessão registrada, para o filtro.
        // Derivado em PHP para não depender de função de data específica do
        // banco (YEAR() não existe no SQLite usado nos testes).
        $anosDisponiveis = Evento::query()
            ->where('tipo', TipoEvento::SESSAO->value)
            ->orderByDesc('inicio_em')
            ->pluck('inicio_em')
            ->map(fn ($data) => (int) $data->format('Y'))
            ->unique()
            ->values();

        $ano = $request->integer('ano') ?: null;

        $eventos = Evento::query()
            ->where('tipo', TipoEvento::SESSAO->value)
            ->when($ano, fn ($query) => $query->whereYear('inicio_em', $ano))
            // Cronológica crescente: o lançamento histórico é feito da sessão
            // mais antiga para a mais recente, acompanhando o livro.
            ->orderBy('inicio_em')
            ->paginate(50)
            ->withQueryString();

        return view('admin.chancelaria.frequencias.selecionar-evento', [
            'eventos' => $eventos,
            'anosDisponiveis' => $anosDisponiveis,
            'ano' => $ano,
            'classesDisponiveis' => collect(ClasseSessao::cases())
                ->mapWithKeys(fn (ClasseSessao $classe) => [$classe->value => $classe->rotulo()]),
        ]);
    }

    /**
     * Registro rápido de uma sessão (geralmente passada), só com o
     * necessário para já cair na tela de lançar a frequência dos Irmãos —
     * ver SalvarSessaoChancelariaRequest.
     */
    public function armazenarSessao(SalvarSessaoChancelariaRequest $request): RedirectResponse
    {
        $this->authorize('chancelaria.criar');

        $dados = $request->validated();
        $titulo = filled($dados['titulo'] ?? null)
            ? $dados['titulo']
            : 'Sessão de '.Carbon::parse($dados['inicio_em'])->translatedFormat('d/m/Y');

        $evento = Evento::create([
            'autor_id' => $request->user()->id,
            'titulo' => $titulo,
            'slug' => Str::slug($titulo).'-'.now()->format('Ymd-His'),
            'tipo' => TipoEvento::SESSAO,
            'sessao_classe' => $dados['sessao_classe'] ?? null,
            'status' => StatusEvento::PUBLICADO,
            'visibilidade' => VisibilidadeEvento::RESTRITA,
            'local' => $dados['local'] ?? null,
            'inicio_em' => $dados['inicio_em'],
        ]);

        RegistradorDeAuditoria::registrar('criar-sessao', 'chancelaria', 'Evento', $evento->id);

        return redirect()
            ->route('admin.chancelaria.frequencias.edit', $evento)
            ->with('sucesso', 'Sessão registrada. Agora lance a presença dos Irmãos.');
    }

    public function edit(Evento $evento): View
    {
        $this->authorize('chancelaria.editar');

        $irmaos = Irmao::query()
            ->orderBy('nome_completo')
            ->get(['id', 'nome_completo', 'cim']);
        $frequencias = ChancelariaFrequencia::query()
            ->where('evento_id', $evento->id)
            ->get()
            ->keyBy('irmao_id');

        return view('admin.chancelaria.frequencias.edit', [
            'evento' => $evento,
            'irmaos' => $irmaos,
            'frequencias' => $frequencias,
            'statusDisponiveis' => collect(StatusFrequencia::cases())->mapWithKeys(fn (StatusFrequencia $status) => [$status->value => $status->rotulo()]),
        ]);
    }

    public function update(SalvarFrequenciaChancelariaRequest $request, Evento $evento): RedirectResponse
    {
        DB::transaction(function () use ($request, $evento): void {
            foreach ($request->input('frequencias', []) as $irmaoId => $dados) {
                // Campo sem lançamento significa "nada a dizer sobre este
                // Irmão", e não "apague o que já foi lançado". Remover um
                // registro é ação explícita — ver limpar().
                if (blank($dados['status'] ?? null)) {
                    continue;
                }

                ChancelariaFrequencia::updateOrCreate(
                    [
                        'evento_id' => $evento->id,
                        'irmao_id' => (int) $irmaoId,
                    ],
                    [
                        'registrado_por_id' => $request->user()->id,
                        'status' => $dados['status'],
                        'observacao' => $dados['observacao'] ?? null,
                    ],
                );
            }

            RegistradorDeAuditoria::registrar('registrar-frequencia', 'chancelaria', 'Evento', $evento->id);
        });

        return back()->with('sucesso', 'Frequência registrada com sucesso.');
    }

    /**
     * Remove o lançamento de um Irmão nesta sessão.
     *
     * Existe para que apagar um registro seja sempre um ato deliberado e
     * auditável, nunca efeito colateral de salvar o formulário.
     */
    public function limpar(Evento $evento, Irmao $irmao): RedirectResponse
    {
        $this->authorize('chancelaria.editar');

        $frequencia = ChancelariaFrequencia::query()
            ->where('evento_id', $evento->id)
            ->where('irmao_id', $irmao->id)
            ->first();

        if ($frequencia === null) {
            return back()->with('erro', 'Este Irmão não possui lançamento nesta sessão.');
        }

        DB::transaction(function () use ($evento, $irmao, $frequencia): void {
            // Guarda o que havia antes: é o que permite reconstruir o
            // lançamento removido a partir da auditoria.
            RegistradorDeAuditoria::registrar(
                acao: 'limpar-frequencia',
                modulo: 'chancelaria',
                entidade: 'Evento',
                entidadeId: $evento->id,
                dadosAnteriores: [
                    'irmao_id' => $irmao->id,
                    'status' => $frequencia->status->value,
                    'observacao' => $frequencia->observacao,
                ],
            );

            $frequencia->delete();
        });

        return back()->with('sucesso', "Lançamento de {$irmao->nome_completo} removido desta sessão.");
    }
}
