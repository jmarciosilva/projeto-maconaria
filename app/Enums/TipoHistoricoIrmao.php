<?php

declare(strict_types=1);

namespace App\Enums;

enum TipoHistoricoIrmao: string
{
    case CARGO = 'cargo';
    case GRAU = 'grau';
    case SITUACAO_CADASTRAL = 'situacao_cadastral';
    case CADASTRAL = 'cadastral';

    public function rotulo(): string
    {
        return match ($this) {
            self::CARGO => 'Alteração de cargo',
            self::GRAU => 'Alteração de grau',
            self::SITUACAO_CADASTRAL => 'Alteração de situação cadastral',
            self::CADASTRAL => 'Alteração cadastral',
        };
    }
}
