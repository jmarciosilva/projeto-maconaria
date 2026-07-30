<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\StatusLancamentoFinanceiro;
use App\Enums\TipoLancamentoFinanceiro;
use App\Exceptions\PeriodoFinanceiroFechadoException;
use App\Http\Controllers\Controller;
use App\Models\Irmao;
use App\Models\TesourariaCategoria;
use App\Models\TesourariaConta;
use App\Models\TesourariaLancamento;
use App\Support\RegistradorDeAuditoria;
use App\Support\Tesouraria\ConversorMoeda;
use App\Support\Tesouraria\ValidadorPeriodoFinanceiro;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class TesourariaMensalidadeController extends Controller
{
    public function create(): View
    {
        $this->authorize('tesouraria.criar');

        return view('admin.tesouraria.mensalidades.create', [
            'categorias' => TesourariaCategoria::query()->where('tipo', TipoLancamentoFinanceiro::RECEITA->value)->pluck('nome', 'id'),
            'contas' => TesourariaConta::query()->where('ativa', true)->pluck('nome', 'id'),
        ]);
    }

    public function store(Request $request, ValidadorPeriodoFinanceiro $periodo): RedirectResponse
    {
        $this->authorize('tesouraria.criar');

        $dados = $request->validate([
            'categoria_id' => ['required', 'exists:tesouraria_categorias,id'],
            'conta_id' => ['required', 'exists:tesouraria_contas,id'],
            'valor' => ['required'],
            'ano' => ['required', 'integer', 'min:2000', 'max:2100'],
            'mes' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        try {
            $data = sprintf('%04d-%02d-01', $dados['ano'], $dados['mes']);

            DB::transaction(function () use ($request, $dados, $periodo, $data): void {
                $periodo->garantirAberto(CarbonImmutable::parse($data));
                $valorCentavos = ConversorMoeda::paraCentavos($dados['valor']);

                foreach (Irmao::query()->orderBy('nome_completo')->get() as $irmao) {
                    TesourariaLancamento::create([
                        'categoria_id' => $dados['categoria_id'],
                        'conta_id' => $dados['conta_id'],
                        'irmao_id' => $irmao->id,
                        'criado_por_id' => $request->user()->id,
                        'tipo' => TipoLancamentoFinanceiro::RECEITA,
                        'status' => StatusLancamentoFinanceiro::PENDENTE,
                        'descricao' => 'Mensalidade '.$dados['mes'].'/'.$dados['ano'].' - '.$irmao->nome_completo,
                        'valor_centavos' => $valorCentavos,
                        'data_competencia' => $data,
                        'data_vencimento' => $data,
                    ]);
                }

                RegistradorDeAuditoria::registrar('gerar-mensalidades', 'tesouraria');
            });
        } catch (PeriodoFinanceiroFechadoException $exception) {
            return back()->withInput()->with('erro', $exception->getMessage());
        }

        return redirect()->route('admin.tesouraria.lancamentos.index')->with('sucesso', 'Mensalidades geradas com sucesso.');
    }
}
