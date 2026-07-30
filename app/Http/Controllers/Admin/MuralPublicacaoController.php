<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\StatusMuralGaleria;
use App\Enums\TipoReacaoMural;
use App\Enums\VisibilidadeMuralGaleria;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SalvarMuralComentarioRequest;
use App\Http\Requests\Admin\SalvarMuralPublicacaoRequest;
use App\Http\Requests\Admin\SalvarMuralReacaoRequest;
use App\Models\MuralComentario;
use App\Models\MuralPublicacao;
use App\Support\RegistradorDeAuditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

final class MuralPublicacaoController extends Controller
{
    public function index(): View
    {
        $this->authorize('mural.visualizar');

        $publicacoes = MuralPublicacao::query()
            ->with('autor')
            ->withCount(['comentarios', 'reacoes'])
            ->latest('created_at')
            ->paginate(20);

        return view('admin.mural.publicacoes.index', compact('publicacoes'));
    }

    public function create(): View
    {
        $this->authorize('mural.criar');

        return view('admin.mural.publicacoes.create', $this->dadosFormulario());
    }

    public function store(SalvarMuralPublicacaoRequest $request): RedirectResponse
    {
        $publicacao = DB::transaction(function () use ($request): MuralPublicacao {
            $publicacao = MuralPublicacao::create([
                ...$this->dadosPersistencia($request),
                'autor_id' => $request->user()->id,
            ]);

            RegistradorDeAuditoria::registrar('criar', 'mural', 'MuralPublicacao', $publicacao->id);

            return $publicacao;
        });

        return redirect()->route('admin.mural.publicacoes.show', $publicacao)->with('sucesso', 'Publicação cadastrada com sucesso.');
    }

    public function show(MuralPublicacao $publicacao): View
    {
        $this->authorize('mural.visualizar');

        $publicacao->load(['autor', 'comentarios.usuario', 'reacoes.usuario']);

        return view('admin.mural.publicacoes.show', [
            'publicacao' => $publicacao,
            'tiposReacao' => collect(TipoReacaoMural::cases())->mapWithKeys(fn (TipoReacaoMural $tipo) => [$tipo->value => $tipo->rotulo()]),
        ]);
    }

    public function edit(MuralPublicacao $publicacao): View
    {
        $this->authorize('mural.editar');

        return view('admin.mural.publicacoes.edit', [...$this->dadosFormulario(), 'publicacao' => $publicacao]);
    }

    public function update(SalvarMuralPublicacaoRequest $request, MuralPublicacao $publicacao): RedirectResponse
    {
        DB::transaction(function () use ($request, $publicacao): void {
            $dadosAnteriores = $publicacao->only(['titulo', 'status', 'visibilidade']);
            $publicacao->fill($this->dadosPersistencia($request, $publicacao))->save();

            RegistradorDeAuditoria::registrar(
                acao: 'editar',
                modulo: 'mural',
                entidade: 'MuralPublicacao',
                entidadeId: $publicacao->id,
                dadosAnteriores: $dadosAnteriores,
                dadosNovos: $publicacao->only(['titulo', 'status', 'visibilidade']),
            );
        });

        return redirect()->route('admin.mural.publicacoes.show', $publicacao)->with('sucesso', 'Publicação atualizada com sucesso.');
    }

    public function aprovarComentario(MuralComentario $comentario): RedirectResponse
    {
        $this->authorize('mural.moderar');

        DB::transaction(function () use ($comentario): void {
            $comentario->update(['aprovado' => true, 'aprovado_em' => now()]);
            RegistradorDeAuditoria::registrar('aprovar-comentario', 'mural', 'MuralComentario', $comentario->id);
        });

        return back()->with('sucesso', 'Comentário aprovado com sucesso.');
    }

    public function comentar(SalvarMuralComentarioRequest $request, MuralPublicacao $publicacao): RedirectResponse
    {
        DB::transaction(function () use ($request, $publicacao): void {
            $comentario = $publicacao->comentarios()->create([
                ...$request->validated(),
                'usuario_id' => $request->user()->id,
                'aprovado' => $request->user()->can('mural.moderar'),
                'aprovado_em' => $request->user()->can('mural.moderar') ? now() : null,
            ]);

            RegistradorDeAuditoria::registrar('comentar', 'mural', 'MuralComentario', $comentario->id);
        });

        return back()->with('sucesso', 'Comentário registrado com sucesso.');
    }

    public function reagir(SalvarMuralReacaoRequest $request, MuralPublicacao $publicacao): RedirectResponse
    {
        DB::transaction(function () use ($request, $publicacao): void {
            $publicacao->reacoes()->updateOrCreate(
                ['usuario_id' => $request->user()->id, 'tipo' => $request->validated('tipo')],
                ['usuario_id' => $request->user()->id, 'tipo' => $request->validated('tipo')],
            );

            RegistradorDeAuditoria::registrar('reagir', 'mural', 'MuralPublicacao', $publicacao->id);
        });

        return back()->with('sucesso', 'Reação registrada com sucesso.');
    }

    private function dadosFormulario(): array
    {
        return [
            'statusDisponiveis' => collect(StatusMuralGaleria::cases())->mapWithKeys(fn (StatusMuralGaleria $status) => [$status->value => $status->rotulo()]),
            'visibilidades' => collect(VisibilidadeMuralGaleria::cases())->mapWithKeys(fn (VisibilidadeMuralGaleria $visibilidade) => [$visibilidade->value => $visibilidade->rotulo()]),
        ];
    }

    private function dadosPersistencia(SalvarMuralPublicacaoRequest $request, ?MuralPublicacao $publicacao = null): array
    {
        $dados = $request->safe()->except('imagem_capa');
        $dados['publicado_em'] = $dados['status'] === StatusMuralGaleria::PUBLICADO->value ? ($publicacao?->publicado_em ?? now()) : null;

        if ($request->hasFile('imagem_capa')) {
            if ($publicacao?->imagem_capa) {
                Storage::disk('public')->delete($publicacao->imagem_capa);
            }

            $dados['imagem_capa'] = $request->file('imagem_capa')->store('mural', 'public');
        }

        return $dados;
    }
}
