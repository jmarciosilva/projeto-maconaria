<?php

declare(strict_types=1);

namespace App\Enums;

enum StatusFrequencia: string
{
    case PRESENTE = 'presente';
    case AUSENTE = 'ausente';
    case JUSTIFICADO = 'justificado';

    public function rotulo(): string
    {
        return match ($this) {
            self::PRESENTE => 'Presente',
            self::AUSENTE => 'Ausente',
            self::JUSTIFICADO => 'Justificado',
        };
    }
}
