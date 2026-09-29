<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\ClasseSessao;
use App\Enums\StatusEvento;
use App\Enums\TipoEvento;
use App\Enums\VisibilidadeEvento;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SalvarSessaoDaChancelariaRequest;
use App\Models\Evento;
use App\Support\RegistradorDeAuditoria;
use App\Support\NormalizadorTexto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Área da Chancelaria para administrar SOMENTE as sessões da Loja
 * (Evento com tipo=SESSAO).
 *
 * Deliberadamente não é o CRUD genérico de Eventos: o Chanceler administra
 * sessões sem ganhar acesso a palestras, jantares e eventos públicos.
 * Sessão continua sendo um Evento tipado — nenhuma entidade nova.
 */
final class ChancelariaSessaoController extends Controller
{
    /**
     * Qualquer rota desta área que receba um Evento que não seja sessão
     * responde 404 — nunca 403, para não confirmar a existência do registro.
     */
    private function garantirSessao(Evento $evento): void
    {
        abort_unless($evento->tipo === TipoEvento::SESSAO, 404);
    }

    /**
     * @return array<string, string>
     */
    private function classes(): array
    {
        return collect(ClasseSessao::cases())
            ->mapWithKeys(fn (ClasseSessao $classe) => [$classe->value => $classe->rotulo()])
            ->all();
    }

    public function index(Request $request): View
    {
        $this->authorize('chancelaria.visualizar');

        $base = Evento::query()->where('tipo', TipoEvento::SESSAO->value);

        $anosDisponiveis = (clone $base)
            ->orderByDesc('inicio_em')
            ->pluck('inicio_em')
            ->map(fn ($data) => (int) $data->format('Y'))
            ->unique()
            ->values();

        $ano = $request->integer('ano') ?: null;
        $classe = $request->string('classe')->toString() ?: null;

        $sessoes = (clone $base)
            ->withCount('frequencias')
            ->when($ano, fn ($query) => $query->whereYear('inicio_em', $ano))
            ->when($classe, fn ($query) => $query->where('sessao_classe', $classe))
            // Crescente: o lançamento histórico acompanha o livro, da sessão
            // mais antiga para a mais recente.
            ->orderBy('inicio_em')
            ->paginate(50)
            ->withQueryString();

        return view('admin.chancelaria.sessoes.index', [
            'sessoes' => $sessoes,
            'anosDisponiveis' => $anosDisponiveis,
            'ano' => $ano,
            'classe' => $classe,
            'classesDisponiveis' => $this->classes(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('chancelaria.criar');

        return view('admin.chancelaria.sessoes.create', [
            'classesDisponiveis' => $this->classes(),
        ]);
    }

    public function store(SalvarSessaoDaChancelariaRequest $request): RedirectResponse
    {
        $this->authorize('chancelaria.criar');

        $dados = $request->validated();
        $evento = Evento::create($this->atributos($dados));

        RegistradorDeAuditoria::registrar(
            acao: 'criar-sessao',
            modulo: 'chancelaria',
            entidade: 'Evento',
            entidadeId: $evento->id,
            dadosNovos: ['inicio_em' => $evento->inicio_em->toDateTimeString(), 'classe' => $evento->sessao_classe?->value],
        );

        if (($dados['acao'] ?? null) === 'frequencia') {
            return redirect()
                ->route('admin.chancelaria.frequencias.edit', $evento)
                ->with('sucesso', 'Sessão cadastrada. Agora lance a presença dos Irmãos.');
        }

        return redirect()
            ->route('admin.chancelaria.sessoes.index')
            ->with('sucesso', 'Sessão cadastrada com sucesso.');
    }

    public function edit(Evento $evento): View
    {
        $this->authorize('chancelaria.editar');
        $this->garantirSessao($evento);

        return view('admin.chancelaria.sessoes.edit', [
            'evento' => $evento,
            'classesDisponiveis' => $this->classes(),
            'totalFrequencias' => $evento->frequencias()->count(),
        ]);
    }

    public function update(SalvarSessaoDaChancelariaRequest $request, Evento $evento): RedirectResponse
    {
        $this->authorize('chancelaria.editar');
        $this->garantirSessao($evento);

        $anteriores = ['inicio_em' => $evento->inicio_em->toDateTimeString(), 'classe' => $evento->sessao_classe?->value];

        // Data, classe e identificação podem mudar sem afetar a integridade:
        // as frequências referenciam o evento_id, que não muda.
        $evento->fill($this->atributos($request->validated(), $evento))->save();

        RegistradorDeAuditoria::registrar(
            acao: 'editar-sessao',
            modulo: 'chancelaria',
            entidade: 'Evento',
            entidadeId: $evento->id,
            dadosAnteriores: $anteriores,
            dadosNovos: ['inicio_em' => $evento->inicio_em->toDateTimeString(), 'classe' => $evento->sessao_classe?->value],
        );

        return redirect()
            ->route('admin.chancelaria.sessoes.index')
            ->with('sucesso', 'Sessão atualizada com sucesso.');
    }

    public function destroy(Evento $evento): RedirectResponse
    {
        $this->authorize('chancelaria.editar');
        $this->garantirSessao($evento);

        $total = $evento->frequencias()->count();

        if ($total > 0) {
            return redirect()
                ->route('admin.chancelaria.sessoes.index')
                ->with('erro', 'Esta sessão possui '.$total.' lançamento(s) de frequência e não pode ser excluída. Remova os lançamentos antes, se realmente for necessário.');
        }

        // Evento usa SoftDeletes: a exclusão é reversível, não destrutiva.
        $evento->delete();

        RegistradorDeAuditoria::registrar(
            acao: 'excluir-sessao',
            modulo: 'chancelaria',
            entidade: 'Evento',
            entidadeId: $evento->id,
        );

        return redirect()
            ->route('admin.chancelaria.sessoes.index')
            ->with('sucesso', 'Sessão removida.');
    }

    /**
     * Monta os atributos gravados. tipo, status e visibilidade são sempre
     * definidos aqui e nunca vêm do request: é o que impede o Chanceler de
     * transformar a sessão em evento comum ou torná-la pública.
     *
     * @param  array<string, mixed>  $dados
     * @return array<string, mixed>
     */
    private function atributos(array $dados, ?Evento $evento = null): array
    {
        $inicio = Carbon::parse($dados['inicio_em']);
        $titulo = filled($dados['titulo'] ?? null)
            ? NormalizadorTexto::paraUtf8($dados['titulo'])
            : 'Sessão de '.$inicio->format('d/m/Y');

        $atributos = [
            'titulo' => $titulo,
            'inicio_em' => $inicio,
            'sessao_classe' => $dados['sessao_classe'] ?? null,
            'local' => $dados['local'] ?? null,
            'tipo' => TipoEvento::SESSAO,
            'status' => StatusEvento::PUBLICADO,
            'visibilidade' => VisibilidadeEvento::RESTRITA,
        ];

        if (! $evento) {
            $atributos['autor_id'] = request()->user()?->id;
            $atributos['slug'] = Str::slug($titulo).'-'.now()->format('Ymd-His');
        }

        return $atributos;
    }
}
