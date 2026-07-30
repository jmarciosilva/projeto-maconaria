<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\StatusLancamentoFinanceiro;
use App\Enums\TipoLancamentoFinanceiro;
use App\Http\Controllers\Controller;
use App\Models\TesourariaConta;
use App\Models\TesourariaLancamento;
use Illuminate\View\View;

final class TesourariaController extends Controller
{
    public function index(): View
    {
        $this->authorize('tesouraria.visualizar');

        $receitas = TesourariaLancamento::query()
            ->where('tipo', TipoLancamentoFinanceiro::RECEITA->value)
            ->where('status', StatusLancamentoFinanceiro::BAIXADO->value)
            ->sum('valor_centavos');
        $despesas = TesourariaLancamento::query()
            ->where('tipo', TipoLancamentoFinanceiro::DESPESA->value)
            ->where('status', StatusLancamentoFinanceiro::BAIXADO->value)
            ->sum('valor_centavos');

        $contas = TesourariaConta::query()->where('ativa', true)->orderBy('nome')->get();
        $pendentes = TesourariaLancamento::query()->where('status', StatusLancamentoFinanceiro::PENDENTE->value)->count();

        return view('admin.tesouraria.index', compact('receitas', 'despesas', 'contas', 'pendentes'));
    }
}
