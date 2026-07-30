<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\StatusLancamentoFinanceiro;
use App\Enums\TipoLancamentoFinanceiro;
use App\Exceptions\PeriodoFinanceiroFechadoException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SalvarTesourariaLancamentoRequest;
use App\Models\Irmao;
use App\Models\TesourariaCategoria;
use App\Models\TesourariaConta;
use App\Models\TesourariaLancamento;
use App\Support\RegistradorDeAuditoria;
use App\Support\Tesouraria\ValidadorPeriodoFinanceiro;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class TesourariaLancamentoController extends Controller
{
    public function index(): View
    {
        $this->authorize('tesouraria.visualizar');

        $lancamentos = TesourariaLancamento::query()
            ->with(['categoria', 'conta', 'irmao'])
            ->latest('data_competencia')
            ->paginate(20);

        return view('admin.tesouraria.lancamentos.index', compact('lancamentos'));
    }

    public function create(): View
    {
        $this->authorize('tesouraria.criar');

        return view('admin.tesouraria.lancamentos.create', $this->dadosFormulario());
    }

    public function store(SalvarTesourariaLancamentoRequest $request, ValidadorPeriodoFinanceiro $periodo): RedirectResponse
    {
        try {
            $lancamento = DB::transaction(function () use ($request, $periodo): TesourariaLancamento {
                $periodo->garantirAberto($request->date('data_competencia'));
                $lancamento = TesourariaLancamento::create([
                    ...$request->validated(),
                    'criado_por_id' => $request->user()->id,
                ]);

                RegistradorDeAuditoria::registrar('criar', 'tesouraria', 'TesourariaLancamento', $lancamento->id);

                return $lancamento;
            });
        } catch (PeriodoFinanceiroFechadoException $exception) {
            return back()->withInput()->with('erro', $exception->getMessage());
        }

        return redirect()->route('admin.tesouraria.lancamentos.edit', $lancamento)->with('sucesso', 'Lançamento cadastrado com sucesso.');
    }

    public function edit(TesourariaLancamento $lancamento): View
    {
        $this->authorize('tesouraria.editar');

        return view('admin.tesouraria.lancamentos.edit', [
            ...$this->dadosFormulario(),
            'lancamento' => $lancamento,
        ]);
    }

    public function update(SalvarTesourariaLancamentoRequest $request, TesourariaLancamento $lancamento, ValidadorPeriodoFinanceiro $periodo): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request, $lancamento, $periodo): void {
                $periodo->garantirAberto($request->date('data_competencia'));
                $lancamento->fill($request->validated())->save();
                RegistradorDeAuditoria::registrar('editar', 'tesouraria', 'TesourariaLancamento', $lancamento->id);
            });
        } catch (PeriodoFinanceiroFechadoException $exception) {
            return back()->withInput()->with('erro', $exception->getMessage());
        }

        return back()->with('sucesso', 'Lançamento atualizado com sucesso.');
    }

    public function aprovar(TesourariaLancamento $lancamento): RedirectResponse
    {
        $this->authorize('tesouraria.aprovar');

        DB::transaction(function () use ($lancamento): void {
            $lancamento->update([
                'status' => StatusLancamentoFinanceiro::APROVADO,
                'aprovado_por_id' => auth()->id(),
                'aprovado_em' => now(),
            ]);
            RegistradorDeAuditoria::registrar('aprovar', 'tesouraria', 'TesourariaLancamento', $lancamento->id);
        });

        return back()->with('sucesso', 'Lançamento aprovado com sucesso.');
    }

    public function baixar(TesourariaLancamento $lancamento, ValidadorPeriodoFinanceiro $periodo): RedirectResponse
    {
        $this->authorize('tesouraria.editar');

        try {
            DB::transaction(function () use ($lancamento, $periodo): void {
                $periodo->garantirAberto($lancamento->data_competencia);
                $lancamento->update([
                    'status' => StatusLancamentoFinanceiro::BAIXADO,
                    'data_pagamento' => now()->toDateString(),
                ]);
                RegistradorDeAuditoria::registrar('baixar', 'tesouraria', 'TesourariaLancamento', $lancamento->id);
            });
        } catch (PeriodoFinanceiroFechadoException $exception) {
            return back()->with('erro', $exception->getMessage());
        }

        return back()->with('sucesso', 'Lançamento baixado com sucesso.');
    }

    private function dadosFormulario(): array
    {
        return [
            'categorias' => TesourariaCategoria::query()->where('ativa', true)->orderBy('nome')->pluck('nome', 'id'),
            'contas' => TesourariaConta::query()->where('ativa', true)->orderBy('nome')->pluck('nome', 'id'),
            'irmaos' => Irmao::query()->orderBy('nome_completo')->pluck('nome_completo', 'id'),
            'tipos' => collect(TipoLancamentoFinanceiro::cases())->mapWithKeys(fn (TipoLancamentoFinanceiro $tipo) => [$tipo->value => $tipo->rotulo()]),
            'statusDisponiveis' => [
                StatusLancamentoFinanceiro::RASCUNHO->value => StatusLancamentoFinanceiro::RASCUNHO->rotulo(),
                StatusLancamentoFinanceiro::PENDENTE->value => StatusLancamentoFinanceiro::PENDENTE->rotulo(),
            ],
        ];
    }
}
