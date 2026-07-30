<?php

declare(strict_types=1);

namespace App\Enums;

enum StatusConfirmacaoPresenca: string
{
    case CONFIRMADO = 'confirmado';
    case CANCELADO = 'cancelado';

    public function rotulo(): string
    {
        return match ($this) {
            self::CONFIRMADO => 'Confirmado',
            self::CANCELADO => 'Cancelado',
        };
    }
}
