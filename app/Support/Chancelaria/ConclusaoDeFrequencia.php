<?php

declare(strict_types=1);

namespace App\Support\Chancelaria;

use App\Enums\AbrangenciaFrequencia;
use App\Enums\StatusFrequencia;
use App\Models\ChancelariaFrequencia;
use App\Models\Evento;
use App\Models\Irmao;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Decide quem deveria ter lançamento numa sessão e o que ainda impede
 * concluir a frequência dela.
 *
 * Regra de abrangência, deliberadamente conservadora e usando só dados que
 * já existem no cadastro:
 *
 *   inicio = data_ingresso_loja ?? data_iniciacao
 *
 *   - sem nenhuma das duas datas .... INDETERMINADO
 *   - inicio depois da sessão ....... NAO_ABRANGIDO (não estava na Loja)
 *   - desligado antes da sessão ..... NAO_ABRANGIDO
 *   - caso contrário ................ ABRANGIDO
 *
 * situacao_cadastral NÃO entra na regra: é o estado de hoje, sem histórico
 * confiável de quando passou a valer. Usá-la removeria da apuração de uma
 * sessão de 2024 um Irmão que está inativo agora mas estava ativo lá, ou
 * seja, reescreveria o passado.
 */
final class ConclusaoDeFrequencia
{
    /**
     * Um lançamento explícito é o fato afirmado pelo Chanceler e encerra a
     * questão, mesmo sem as datas no cadastro.
     */
    public static function abrangencia(Irmao $irmao, CarbonInterface $dataSessao): AbrangenciaFrequencia
    {
        // Comparação por dia: quem ingressou na própria data da sessão está
        // abrangido, e quem se desligou nela ainda está.
        $dia = $dataSessao->copy()->startOfDay();

        $inicio = $irmao->data_ingresso_loja ?? $irmao->data_iniciacao;

        if ($inicio === null) {
            return AbrangenciaFrequencia::INDETERMINADO;
        }

        if ($inicio->copy()->startOfDay()->greaterThan($dia)) {
            return AbrangenciaFrequencia::NAO_ABRANGIDO;
        }

        $desligamento = $irmao->data_desligamento;

        if ($desligamento !== null && $desligamento->copy()->startOfDay()->lessThan($dia)) {
            return AbrangenciaFrequencia::NAO_ABRANGIDO;
        }

        return AbrangenciaFrequencia::ABRANGIDO;
    }

    /**
     * O que ainda impede concluir a frequência desta sessão.
     *
     * @return array{
     *     sem_lancamento: Collection<int, Irmao>,
     *     indeterminados: Collection<int, Irmao>,
     *     sem_motivo: Collection<int, Irmao>
     * }
     */
    public static function pendencias(Evento $evento): array
    {
        $irmaos = Irmao::query()->orderBy('nome_completo')->get();

        $frequencias = ChancelariaFrequencia::query()
            ->where('evento_id', $evento->id)
            ->get()
            ->keyBy('irmao_id');

        $semLancamento = collect();
        $indeterminados = collect();
        $semMotivo = collect();

        foreach ($irmaos as $irmao) {
            $frequencia = $frequencias->get($irmao->id);

            if ($frequencia !== null) {
                if ($frequencia->status === StatusFrequencia::JUSTIFICADO && blank($frequencia->observacao)) {
                    $semMotivo->push($irmao);
                }

                continue;
            }

            match (self::abrangencia($irmao, $evento->inicio_em)) {
                AbrangenciaFrequencia::ABRANGIDO => $semLancamento->push($irmao),
                AbrangenciaFrequencia::INDETERMINADO => $indeterminados->push($irmao),
                AbrangenciaFrequencia::NAO_ABRANGIDO => null,
            };
        }

        return [
            'sem_lancamento' => $semLancamento,
            'indeterminados' => $indeterminados,
            'sem_motivo' => $semMotivo,
        ];
    }

    /**
     * @param  array{sem_lancamento: Collection<int, Irmao>, indeterminados: Collection<int, Irmao>, sem_motivo: Collection<int, Irmao>}  $pendencias
     */
    public static function possuiPendencias(array $pendencias): bool
    {
        return $pendencias['sem_lancamento']->isNotEmpty()
            || $pendencias['indeterminados']->isNotEmpty()
            || $pendencias['sem_motivo']->isNotEmpty();
    }

    /**
     * Mensagem única para a tela, nomeando os Irmãos quando são poucos —
     * saber quem falta vale mais que saber quantos.
     *
     * @param  array{sem_lancamento: Collection<int, Irmao>, indeterminados: Collection<int, Irmao>, sem_motivo: Collection<int, Irmao>}  $pendencias
     */
    public static function mensagemDePendencias(array $pendencias): string
    {
        $partes = [];

        if (($total = $pendencias['sem_lancamento']->count()) > 0) {
            $partes[] = "Existem {$total} ".($total === 1 ? 'Irmão' : 'Irmãos')
                .' sem lançamento de frequência'.self::nomes($pendencias['sem_lancamento']).'.';
        }

        if (($total = $pendencias['sem_motivo']->count()) > 0) {
            $partes[] = "Existem {$total} ".($total === 1 ? 'justificativa' : 'justificativas')
                .' sem motivo informado'.self::nomes($pendencias['sem_motivo']).'.';
        }

        if (($total = $pendencias['indeterminados']->count()) > 0) {
            $partes[] = "Existem {$total} ".($total === 1 ? 'Irmão' : 'Irmãos')
                .' sem data de ingresso, então não é possível determinar se participavam desta sessão'
                .self::nomes($pendencias['indeterminados'])
                .'. Informe a data no cadastro ou registre a frequência manualmente.';
        }

        return implode(' ', $partes);
    }

    /**
     * @param  Collection<int, Irmao>  $irmaos
     */
    private static function nomes(Collection $irmaos): string
    {
        if ($irmaos->count() > 5) {
            return '';
        }

        return ' ('.$irmaos->pluck('nome_completo')->implode(', ').')';
    }
}
