<?php

declare(strict_types=1);

namespace App\Enums;

enum StatusLancamentoFinanceiro: string
{
    case RASCUNHO = 'rascunho';
    case PENDENTE = 'pendente';
    case APROVADO = 'aprovado';
    case BAIXADO = 'baixado';
    case CANCELADO = 'cancelado';

    public function rotulo(): string
    {
        return match ($this) {
            self::RASCUNHO => 'Rascunho',
            self::PENDENTE => 'Pendente',
            self::APROVADO => 'Aprovado',
            self::BAIXADO => 'Baixado',
            self::CANCELADO => 'Cancelado',
        };
    }
}
