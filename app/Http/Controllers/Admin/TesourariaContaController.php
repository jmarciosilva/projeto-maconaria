<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\TipoContaFinanceira;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SalvarTesourariaContaRequest;
use App\Models\TesourariaConta;
use App\Support\RegistradorDeAuditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class TesourariaContaController extends Controller
{
    public function index(): View
    {
        $this->authorize('tesouraria.visualizar');

        $contas = TesourariaConta::query()->orderBy('nome')->get();
        $tipos = collect(TipoContaFinanceira::cases())->mapWithKeys(fn (TipoContaFinanceira $tipo) => [$tipo->value => $tipo->rotulo()]);

        return view('admin.tesouraria.contas.index', compact('contas', 'tipos'));
    }

    public function store(SalvarTesourariaContaRequest $request): RedirectResponse
    {
        $conta = TesourariaConta::create($request->validated());

        RegistradorDeAuditoria::registrar('criar', 'tesouraria', 'TesourariaConta', $conta->id);

        return back()->with('sucesso', 'Conta cadastrada com sucesso.');
    }
}
