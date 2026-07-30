<?php

declare(strict_types=1);

namespace App\Enums;

enum TipoLancamentoFinanceiro: string
{
    case RECEITA = 'receita';
    case DESPESA = 'despesa';

    public function rotulo(): string
    {
        return match ($this) {
            self::RECEITA => 'Receita',
            self::DESPESA => 'Despesa',
        };
    }
}
