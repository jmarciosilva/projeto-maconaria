<?php

declare(strict_types=1);

namespace App\Support\Chancelaria;

use App\Enums\StatusFrequencia;
use App\Enums\TipoEvento;
use App\Models\ChancelariaFrequencia;
use App\Models\Evento;
use App\Models\Irmao;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Apuração de frequência do MVP histórico.
 *
 * Regra vigente (decidida para este MVP, ainda não parametrizável):
 *
 * - "Sessões consideradas" são apenas as sessões do período em que existe
 *   registro EXPLÍCITO de frequência para aquele Irmão. A ausência de
 *   registro significa "não informado" — nunca falta. É isso que impede o
 *   Irmão que ingressou no meio do ano de aparecer com faltas de janeiro.
 * - PRESENTE conta no numerador e no denominador.
 * - AUSENTE e JUSTIFICADO contam somente no denominador. Justificado é
 *   mantido separado no relatório para preservar a informação histórica,
 *   mas não aumenta o percentual.
 * - Sem sessões consideradas não há percentual (não é 0%).
 */
final class CalculadoraFrequencia
{
    public const LIMITE_INDICADOR = 50.0;

    /**
     * @return array{
     *     total_sessoes: int,
     *     linhas: Collection<int, array<string, mixed>>
     * }
     */
    public function apurar(CarbonInterface $inicio, CarbonInterface $fim, string $ordenarPor = 'nome'): array
    {
        $sessoes = Evento::query()
            ->where('tipo', TipoEvento::SESSAO->value)
            ->whereBetween('inicio_em', [$inicio, $fim])
            ->pluck('id');

        $totalSessoes = $sessoes->count();

        $registros = $sessoes->isEmpty()
            ? collect()
            : ChancelariaFrequencia::query()
                ->whereIn('evento_id', $sessoes)
                ->selectRaw('irmao_id, status, count(*) as total')
                ->groupBy('irmao_id', 'status')
                ->get()
                ->groupBy('irmao_id');

        $linhas = Irmao::query()
            ->orderBy('nome_completo')
            ->get(['id', 'nome_completo', 'cim'])
            ->map(function (Irmao $irmao) use ($registros, $totalSessoes): array {
                // O model faz cast de status para enum, então normalizamos para
                // string antes de usar como chave de array.
                $porStatus = $registros->get($irmao->id, collect())
                    ->mapWithKeys(fn ($registro) => [
                        ($registro->status instanceof StatusFrequencia
                            ? $registro->status->value
                            : (string) $registro->status) => (int) $registro->total,
                    ]);

                $presencas = (int) ($porStatus[StatusFrequencia::PRESENTE->value] ?? 0);
                $ausencias = (int) ($porStatus[StatusFrequencia::AUSENTE->value] ?? 0);
                $justificadas = (int) ($porStatus[StatusFrequencia::JUSTIFICADO->value] ?? 0);

                $consideradas = $presencas + $ausencias + $justificadas;

                // Sem sessões consideradas não existe percentual: exibir 0%
                // afirmaria ausência total, quando na verdade nada foi apurado.
                $percentual = $consideradas > 0
                    ? round($presencas / $consideradas * 100, 1)
                    : null;

                return [
                    'irmao_id' => $irmao->id,
                    'cim' => $irmao->cim,
                    'nome' => $irmao->nome_completo,
                    'consideradas' => $consideradas,
                    'presencas' => $presencas,
                    'ausencias' => $ausencias,
                    'justificadas' => $justificadas,
                    'nao_informadas' => max($totalSessoes - $consideradas, 0),
                    'percentual' => $percentual,
                    'abaixo_do_limite' => $percentual !== null && $percentual < self::LIMITE_INDICADOR,
                ];
            });

        if ($ordenarPor === 'frequencia') {
            // Quem não tem apuração vai para o fim da lista, para não se
            // confundir com frequência baixa.
            $linhas = $linhas->sortBy(fn (array $linha) => $linha['percentual'] ?? PHP_INT_MAX)->values();
        }

        return [
            'total_sessoes' => $totalSessoes,
            'linhas' => $linhas,
        ];
    }
}
