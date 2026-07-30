<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\TipoLancamentoFinanceiro;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SalvarTesourariaCategoriaRequest;
use App\Models\TesourariaCategoria;
use App\Support\RegistradorDeAuditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class TesourariaCategoriaController extends Controller
{
    public function index(): View
    {
        $this->authorize('tesouraria.visualizar');

        $categorias = TesourariaCategoria::query()->orderBy('tipo')->orderBy('nome')->get();
        $tipos = collect(TipoLancamentoFinanceiro::cases())->mapWithKeys(fn (TipoLancamentoFinanceiro $tipo) => [$tipo->value => $tipo->rotulo()]);

        return view('admin.tesouraria.categorias.index', compact('categorias', 'tipos'));
    }

    public function store(SalvarTesourariaCategoriaRequest $request): RedirectResponse
    {
        $categoria = TesourariaCategoria::create([...$request->validated(), 'ativa' => $request->boolean('ativa')]);

        RegistradorDeAuditoria::registrar('criar', 'tesouraria', 'TesourariaCategoria', $categoria->id);

        return back()->with('sucesso', 'Categoria cadastrada com sucesso.');
    }
}
