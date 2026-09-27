<?php

namespace App\Http\Controllers\AreaRestrita;

use App\Enums\CategoriaRecadoPainel;
use App\Enums\StatusFrequencia;
use App\Http\Controllers\Controller;
use App\Models\ChancelariaFrequencia;
use App\Models\Evento;
use App\Models\PainelRecado;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class PainelController extends Controller
{
    public function index(): View
    {
        $proximosEventos = Evento::query()
            ->with(['confirmacoes' => fn ($query) => $query->where('usuario_id', Auth::id())])
            ->visivelNaAreaRestrita()
            ->futuro()
            ->orderBy('inicio_em')
            ->limit(5)
            ->get();

        $recadosPorCategoria = PainelRecado::query()
            ->visiveis()
            ->latest('publicado_em')
            ->get()
            ->groupBy(fn (PainelRecado $recado) => $recado->categoria->value);

        $recadosGerais = $recadosPorCategoria->get(CategoriaRecadoPainel::GERAL->value, collect());

        $frequencia = $this->calcularFrequencia();

        return view('area-restrita.painel', [
            'proximosEventos' => $proximosEventos,
            'recadosPorCategoria' => $recadosPorCategoria,
            'recadosGerais' => $recadosGerais,
            'frequencia' => $frequencia,
            'graficoFrequencia' => $this->graficoFrequenciaMock(),
            'financeiro' => $this->financeiroMock(),
        ]);
    }

    /**
     * Dados ilustrativos para os 4 gráficos de frequência do painel (mensal e
     * anual, em percentual e em número de presenças/faltas). Servem apenas
     * para a apresentação do novo layout do Mural da Loja aos irmãos
     * responsáveis pelo projeto; quando aprovado, substituir por uma
     * agregação real de ChancelariaFrequencia por mês/ano.
     *
     * @return array{
     *     mensal: array<int, array{mes: string, presencas: int, faltas: int, total: int, percentual: int}>,
     *     anual: array<int, array{ano: int, presencas: int, faltas: int, total: int, percentual: int}>,
     *     maximoSessoesMes: int,
     *     maximoSessoesAno: int,
     * }
     */
    private function graficoFrequenciaMock(): array
    {
        $meses = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
        // Presenças/faltas por sessão realizada no mês (a Loja costuma se
        // reunir duas vezes por mês, com recesso em julho e dezembro).
        $presencasMensais = [2, 2, 1, 2, 2, 2, 1, 1, 2, 2, 1, 1];
        $faltasMensais = [0, 0, 1, 0, 0, 0, 0, 1, 0, 0, 1, 0];

        $anoAtual = (int) now()->format('Y');
        $presencasAnuais = [13, 14, 15, 17, 16];
        $faltasAnuais = [6, 5, 4, 2, 3];

        $mensal = collect($meses)
            ->map(fn (string $mes, int $indice) => $this->linhaFrequencia(
                ['mes' => $mes],
                $presencasMensais[$indice],
                $faltasMensais[$indice],
            ))
            ->all();

        $anual = collect($presencasAnuais)
            ->map(fn (int $presencas, int $indice) => $this->linhaFrequencia(
                ['ano' => $anoAtual - (count($presencasAnuais) - 1) + $indice],
                $presencas,
                $faltasAnuais[$indice],
            ))
            ->all();

        return [
            'mensal' => $mensal,
            'anual' => $anual,
            'maximoSessoesMes' => max(array_map(fn (array $linha) => $linha['total'], $mensal)),
            'maximoSessoesAno' => max(array_map(fn (array $linha) => $linha['total'], $anual)),
            'mediaMensal' => (int) round(collect($mensal)->avg('percentual')),
            'mediaAnual' => (int) round(collect($anual)->avg('percentual')),
        ];
    }

    private function linhaFrequencia(array $rotulo, int $presencas, int $faltas): array
    {
        $total = $presencas + $faltas;

        return [
            ...$rotulo,
            'presencas' => $presencas,
            'faltas' => $faltas,
            'total' => $total,
            'percentual' => $total > 0 ? (int) round($presencas / $total * 100) : 0,
        ];
    }

    /**
     * Dados ilustrativos da vida financeira do irmão na Loja: extrato de
     * pagamento das mensalidades do ano, o percentual de débito em aberto e o
     * detalhamento de cada débito pendente (mensalidade, eventos etc.).
     * Servem apenas para a apresentação do novo layout do Mural da Loja;
     * quando aprovado, substituir por uma consulta real a
     * TesourariaLancamento filtrada pelo irmão logado.
     *
     * @return array{
     *     extratoMensal: array<int, array{mes: string, pago: float}>,
     *     valorMensalidade: float,
     *     totalPago: float,
     *     totalDevido: float,
     *     totalEmDebito: float,
     *     percentualEmDia: int,
     *     percentualEmDebito: int,
     *     itensEmDebito: array<int, array{tipo: string, descricao: string, valor: float}>,
     * }
     */
    private function financeiroMock(): array
    {
        $meses = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
        $valorMensalidade = 60.0;
        // 0 representa mês em aberto (mensalidade ainda não paga).
        $pagamentosMensais = [60, 60, 60, 60, 0, 60, 60, 60, 60, 60, 60, 60];

        $extratoMensal = collect($meses)
            ->map(fn (string $mes, int $indice) => ['mes' => $mes, 'pago' => (float) $pagamentosMensais[$indice]])
            ->all();

        // Além das mensalidades, débitos avulsos (convites de jantares e
        // eventos ritualísticos, por exemplo) também entram no total devido,
        // mesmo não aparecendo no extrato mensal de mensalidades acima.
        $itensEmDebito = [
            ['tipo' => 'Mensalidade', 'descricao' => 'Mensalidade de Maio/'.now()->format('Y'), 'valor' => $valorMensalidade],
            ['tipo' => 'Evento', 'descricao' => 'Convite não pago — Jantar Ritualístico de Aniversário da Loja', 'valor' => 45.0],
        ];

        $totalDevidoMensalidades = $valorMensalidade * count($meses);
        $totalPago = array_sum($pagamentosMensais);
        $totalEmDebito = collect($itensEmDebito)->sum('valor');
        $totalDevido = $totalPago + $totalEmDebito;
        $percentualEmDebito = $totalDevido > 0 ? (int) round($totalEmDebito / $totalDevido * 100) : 0;

        return [
            'extratoMensal' => $extratoMensal,
            'valorMensalidade' => $valorMensalidade,
            'totalPago' => $totalPago,
            'totalDevido' => $totalDevido,
            'totalEmDebito' => $totalEmDebito,
            'percentualEmDia' => 100 - $percentualEmDebito,
            'percentualEmDebito' => $percentualEmDebito,
            'itensEmDebito' => $itensEmDebito,
        ];
    }

    /**
     * @return array{presentes: int, total: int, percentual: int}|null
     */
    private function calcularFrequencia(): ?array
    {
        $irmao = Auth::user()->irmao;

        if ($irmao === null) {
            return null;
        }

        $frequencias = ChancelariaFrequencia::query()
            ->where('irmao_id', $irmao->id)
            ->whereHas('evento', fn ($query) => $query->whereBetween('inicio_em', [now()->startOfYear(), now()]))
            ->get();

        $total = $frequencias->count();

        if ($total === 0) {
            return null;
        }

        $presentes = $frequencias->where('status', StatusFrequencia::PRESENTE)->count();

        return [
            'presentes' => $presentes,
            'total' => $total,
            'percentual' => (int) round($presentes / $total * 100),
        ];
    }
}
