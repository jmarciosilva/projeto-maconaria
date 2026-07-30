<?php

declare(strict_types=1);

namespace App\Enums;

enum TipoContaFinanceira: string
{
    case CAIXA = 'caixa';
    case BANCO = 'banco';

    public function rotulo(): string
    {
        return match ($this) {
            self::CAIXA => 'Caixa',
            self::BANCO => 'Banco',
        };
    }
}
