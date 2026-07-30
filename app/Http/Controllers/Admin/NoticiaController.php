<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\StatusNoticia;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AtualizarNoticiaRequest;
use App\Http\Requests\Admin\CriarNoticiaRequest;
use App\Models\Noticia;
use App\Models\NoticiaCategoria;
use App\Models\NoticiaTag;
use App\Support\ProcessadorConteudoNoticia;
use App\Support\RegistradorDeAuditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

final class NoticiaController extends Controller
{
    public function index(): View
    {
        $this->authorize('noticias.visualizar');

        $noticias = Noticia::query()
            ->with(['categoria', 'autor'])
            ->latest()
            ->paginate(15);

        return view('admin.noticias.index', compact('noticias'));
    }

    public function create(): View
    {
        $this->authorize('noticias.criar');

        return view('admin.noticias.create', $this->dadosFormulario());
    }

    public function store(CriarNoticiaRequest $request): RedirectResponse
    {
        $noticia = DB::transaction(function () use ($request): Noticia {
            $dados = $request->safe()->except('tags', 'conteudo', 'imagem_capa');
            $dados['autor_id'] = $request->user()->id;
            $dados['conteudo'] = ProcessadorConteudoNoticia::prepararParaSalvar($request->input('conteudo'));
            $dados['destaque'] = $request->boolean('destaque');

            if ($request->hasFile('imagem_capa')) {
                $dados['imagem_capa'] = $request->file('imagem_capa')->store('noticias', 'public');
            }

            if ($dados['status'] === StatusNoticia::PUBLICADA->value && empty($dados['publicado_em'])) {
                $dados['publicado_em'] = now();
            }

            $noticia = Noticia::create($dados);
            $noticia->tags()->sync($request->input('tags', []));
            $this->registrarVersao($noticia);

            RegistradorDeAuditoria::registrar('criar', 'noticias', 'Noticia', $noticia->id);

            return $noticia;
        });

        return redirect()
            ->route('admin.noticias.edit', $noticia)
            ->with('sucesso', 'Notícia cadastrada com sucesso.');
    }

    public function edit(Noticia $noticia): View
    {
        $this->authorize('noticias.editar');

        $noticia->load('tags');

        return view('admin.noticias.edit', [
            ...$this->dadosFormulario(),
            'noticia' => $noticia,
        ]);
    }

    public function update(AtualizarNoticiaRequest $request, Noticia $noticia): RedirectResponse
    {
        DB::transaction(function () use ($request, $noticia): void {
            $dadosAnteriores = $noticia->only(['titulo', 'slug', 'status', 'visibilidade', 'destaque']);
            $dados = $request->safe()->except('tags', 'conteudo', 'imagem_capa');
            $dados['conteudo'] = ProcessadorConteudoNoticia::prepararParaSalvar($request->input('conteudo'));
            $dados['destaque'] = $request->boolean('destaque');

            if ($request->hasFile('imagem_capa')) {
                if ($noticia->imagem_capa) {
                    Storage::disk('public')->delete($noticia->imagem_capa);
                }

                $dados['imagem_capa'] = $request->file('imagem_capa')->store('noticias', 'public');
            }

            if ($dados['status'] === StatusNoticia::PUBLICADA->value && empty($dados['publicado_em'])) {
                $dados['publicado_em'] = now();
            }

            $noticia->fill($dados)->save();
            $noticia->tags()->sync($request->input('tags', []));
            $this->registrarVersao($noticia);

            RegistradorDeAuditoria::registrar(
                acao: 'editar',
                modulo: 'noticias',
                entidade: 'Noticia',
                entidadeId: $noticia->id,
                dadosAnteriores: $dadosAnteriores,
                dadosNovos: $noticia->only(['titulo', 'slug', 'status', 'visibilidade', 'destaque']),
            );
        });

        return redirect()
            ->route('admin.noticias.edit', $noticia)
            ->with('sucesso', 'Notícia atualizada com sucesso.');
    }

    public function destroy(Noticia $noticia): RedirectResponse
    {
        $this->authorize('noticias.excluir');

        $noticia->delete();

        RegistradorDeAuditoria::registrar('excluir', 'noticias', 'Noticia', $noticia->id);

        return redirect()
            ->route('admin.noticias.index')
            ->with('sucesso', 'Notícia removida com sucesso.');
    }

    private function dadosFormulario(): array
    {
        return [
            'categorias' => NoticiaCategoria::query()->where('ativa', true)->orderBy('nome')->pluck('nome', 'id'),
            'tags' => NoticiaTag::query()->orderBy('nome')->get(),
            'statusDisponiveis' => collect(StatusNoticia::cases())->mapWithKeys(fn (StatusNoticia $status) => [$status->value => $status->rotulo()]),
        ];
    }

    private function registrarVersao(Noticia $noticia): void
    {
        $proximaVersao = ((int) $noticia->versoes()->max('versao')) + 1;

        $noticia->versoes()->create([
            'usuario_id' => auth()->id(),
            'versao' => $proximaVersao,
            'titulo' => $noticia->titulo,
            'resumo' => $noticia->resumo,
            'conteudo' => $noticia->conteudo,
            'imagem_capa' => $noticia->imagem_capa,
            'status' => $noticia->status,
            'visibilidade' => $noticia->visibilidade,
            'destaque' => $noticia->destaque,
            'publicado_em' => $noticia->publicado_em,
            'agendado_para' => $noticia->agendado_para,
        ]);
    }
}
