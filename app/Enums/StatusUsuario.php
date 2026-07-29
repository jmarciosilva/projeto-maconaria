<?php

declare(strict_types=1);

namespace App\Enums;

enum StatusUsuario: string
{
    case ATIVO = 'ativo';
    case INATIVO = 'inativo';

    public function rotulo(): string
    {
        return match ($this) {
            self::ATIVO => 'Ativo',
            self::INATIVO => 'Inativo',
        };
    }
}
