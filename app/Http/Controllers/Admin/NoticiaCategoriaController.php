<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NoticiaCategoria;
use App\Support\NormalizadorTexto;
use App\Support\RegistradorDeAuditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class NoticiaCategoriaController extends Controller
{
    public function index(): View
    {
        $this->authorize('noticias.visualizar');

        $categorias = NoticiaCategoria::query()->withCount('noticias')->orderBy('nome')->get();

        return view('admin.noticia-categorias.index', compact('categorias'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('noticias.editar');

        $dados = $this->validar($request);
        $categoria = NoticiaCategoria::create($dados);

        RegistradorDeAuditoria::registrar('criar', 'noticias', 'NoticiaCategoria', $categoria->id);

        return back()->with('sucesso', 'Categoria cadastrada com sucesso.');
    }

    public function update(Request $request, NoticiaCategoria $categoria): RedirectResponse
    {
        $this->authorize('noticias.editar');

        $categoria->fill($this->validar($request, $categoria))->save();

        RegistradorDeAuditoria::registrar('editar', 'noticias', 'NoticiaCategoria', $categoria->id);

        return back()->with('sucesso', 'Categoria atualizada com sucesso.');
    }

    public function destroy(NoticiaCategoria $categoria): RedirectResponse
    {
        $this->authorize('noticias.excluir');

        $categoria->delete();

        RegistradorDeAuditoria::registrar('excluir', 'noticias', 'NoticiaCategoria', $categoria->id);

        return back()->with('sucesso', 'Categoria removida com sucesso.');
    }

    private function validar(Request $request, ?NoticiaCategoria $categoria = null): array
    {
        $nome = NormalizadorTexto::paraUtf8((string) $request->input('nome'));
        $slug = $request->filled('slug') ? Str::slug((string) $request->input('slug')) : Str::slug($nome);

        $request->merge([
            'nome' => $nome,
            'slug' => $slug,
            'ativa' => $request->boolean('ativa'),
        ]);

        return $request->validate([
            'nome' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:140', 'regex:/^[a-z0-9]+(-[a-z0-9]+)*$/', Rule::unique('noticia_categorias', 'slug')->ignore($categoria)],
            'descricao' => ['nullable', 'string', 'max:500'],
            'ativa' => ['boolean'],
        ]);
    }
}
