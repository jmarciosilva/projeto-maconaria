<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NoticiaTag;
use App\Support\NormalizadorTexto;
use App\Support\RegistradorDeAuditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class NoticiaTagController extends Controller
{
    public function index(): View
    {
        $this->authorize('noticias.visualizar');

        $tags = NoticiaTag::query()->withCount('noticias')->orderBy('nome')->get();

        return view('admin.noticia-tags.index', compact('tags'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('noticias.editar');

        $tag = NoticiaTag::create($this->validar($request));

        RegistradorDeAuditoria::registrar('criar', 'noticias', 'NoticiaTag', $tag->id);

        return back()->with('sucesso', 'Tag cadastrada com sucesso.');
    }

    public function update(Request $request, NoticiaTag $tag): RedirectResponse
    {
        $this->authorize('noticias.editar');

        $tag->fill($this->validar($request, $tag))->save();

        RegistradorDeAuditoria::registrar('editar', 'noticias', 'NoticiaTag', $tag->id);

        return back()->with('sucesso', 'Tag atualizada com sucesso.');
    }

    public function destroy(NoticiaTag $tag): RedirectResponse
    {
        $this->authorize('noticias.excluir');

        $tag->delete();

        RegistradorDeAuditoria::registrar('excluir', 'noticias', 'NoticiaTag', $tag->id);

        return back()->with('sucesso', 'Tag removida com sucesso.');
    }

    private function validar(Request $request, ?NoticiaTag $tag = null): array
    {
        $nome = NormalizadorTexto::paraUtf8((string) $request->input('nome'));
        $slug = $request->filled('slug') ? Str::slug((string) $request->input('slug')) : Str::slug($nome);

        $request->merge(['nome' => $nome, 'slug' => $slug]);

        return $request->validate([
            'nome' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:140', 'regex:/^[a-z0-9]+(-[a-z0-9]+)*$/', Rule::unique('noticia_tags', 'slug')->ignore($tag)],
        ]);
    }
}
