<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class ChancelariaFrequenciaController extends Controller
{
    public function selecionarEvento(): View
    {
        $this->authorize('chancelaria.visualizar');

        $eventos = Evento::query()
            ->orderByDesc('inicio_em')
            ->limit(30)
            ->get();

        return view('admin.chancelaria.frequencias.selecionar-evento', compact('eventos'));
    }

    /**
     * Registro rápido de uma sessão (geralmente passada), só com o
     * necessário para já cair na tela de lançar a frequência dos Irmãos —
     * ver SalvarSessaoChancelariaRequest.
     */
    public function armazenarSessao(SalvarSessaoChancelariaRequest $request): RedirectResponse
    {
        $dados = $request->validated();
        $titulo = filled($dados['titulo'] ?? null)
            ? $dados['titulo']
            : 'Sessão de '.Carbon::parse($dados['inicio_em'])->translatedFormat('d/m/Y');

        $evento = Evento::create([
            'autor_id' => $request->user()->id,
            'titulo' => $titulo,
            'slug' => Str::slug($titulo).'-'.now()->format('Ymd-His'),
            'tipo' => TipoEvento::SESSAO,
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

        $irmaos = Irmao::query()->orderBy('nome_completo')->get();
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
                if (blank($dados['status'] ?? null)) {
                    ChancelariaFrequencia::query()
                        ->where('evento_id', $evento->id)
                        ->where('irmao_id', $irmaoId)
                        ->delete();

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
}
