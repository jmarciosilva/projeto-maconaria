<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AtualizarPaginaInstitucionalRequest;
use App\Http\Requests\Admin\CriarPaginaInstitucionalRequest;
use App\Models\PaginaInstitucional;
use App\Support\ProcessadorConteudoInstitucional;
use App\Support\RegistradorDeAuditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class PaginaInstitucionalController extends Controller
{
    public function index(): View
    {
        $this->authorize('cms.visualizar');

        $paginas = PaginaInstitucional::query()->orderBy('titulo')->get();

        return view('admin.paginas-institucionais.index', compact('paginas'));
    }

    public function create(): View
    {
        $this->authorize('cms.editar');

        return view('admin.paginas-institucionais.create');
    }

    public function store(CriarPaginaInstitucionalRequest $request): RedirectResponse
    {
        $dados = $request->safe()->except('conteudo');
        $dados['publicado'] = $request->boolean('publicado');
        $dados['conteudo'] = ProcessadorConteudoInstitucional::prepararParaSalvar($request->input('conteudo'));

        $pagina = PaginaInstitucional::create($dados);

        RegistradorDeAuditoria::registrar(
            acao: 'criar',
            modulo: 'cms',
            entidade: 'PaginaInstitucional',
            entidadeId: $pagina->id,
        );

        return redirect()
            ->route('admin.paginas-institucionais.index')
            ->with('sucesso', 'Página institucional cadastrada com sucesso.');
    }

    public function edit(PaginaInstitucional $pagina): View
    {
        $this->authorize('cms.editar');

        return view('admin.paginas-institucionais.edit', compact('pagina'));
    }

    public function update(AtualizarPaginaInstitucionalRequest $request, PaginaInstitucional $pagina): RedirectResponse
    {
        $dados = $request->safe()->except('conteudo');
        $dados['publicado'] = $request->boolean('publicado');
        $dados['conteudo'] = ProcessadorConteudoInstitucional::prepararParaSalvar($request->input('conteudo'));

        $pagina->fill($dados)->save();

        RegistradorDeAuditoria::registrar(
            acao: 'editar',
            modulo: 'cms',
            entidade: 'PaginaInstitucional',
            entidadeId: $pagina->id,
        );

        return redirect()
            ->route('admin.paginas-institucionais.index')
            ->with('sucesso', 'Página institucional atualizada com sucesso.');
    }

    public function destroy(PaginaInstitucional $pagina): RedirectResponse
    {
        $this->authorize('cms.editar');

        $pagina->delete();

        RegistradorDeAuditoria::registrar('excluir', 'cms', 'PaginaInstitucional', $pagina->id);

        return redirect()
            ->route('admin.paginas-institucionais.index')
            ->with('sucesso', 'Página institucional removida com sucesso.');
    }
}
