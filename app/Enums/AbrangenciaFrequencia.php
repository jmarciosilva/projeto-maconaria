<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Responde: "na data desta sessão, este Irmão fazia parte da Loja e deveria
 * entrar na apuração de frequência?"
 *
 * Não é um status de frequência — os únicos estados de frequência continuam
 * sendo presente, ausente e justificado (ver StatusFrequencia). Isto apenas
 * delimita de quem se espera um lançamento.
 */
enum AbrangenciaFrequencia: string
{
    case ABRANGIDO = 'abrangido';
    case NAO_ABRANGIDO = 'nao_abrangido';

    /**
     * Faltam datas no cadastro para decidir. Nunca deve virar ausência
     * automaticamente: exige cadastro da data ou lançamento explícito.
     */
    case INDETERMINADO = 'indeterminado';

    public function rotulo(): string
    {
        return match ($this) {
            self::ABRANGIDO => 'Abrangido pela apuração',
            self::NAO_ABRANGIDO => 'Fora da apuração nesta data',
            self::INDETERMINADO => 'Participação indeterminada',
        };
    }
}
