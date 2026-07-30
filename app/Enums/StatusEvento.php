<?php

declare(strict_types=1);

namespace App\Enums;

enum StatusEvento: string
{
    case RASCUNHO = 'rascunho';
    case PUBLICADO = 'publicado';
    case CANCELADO = 'cancelado';

    public function rotulo(): string
    {
        return match ($this) {
            self::RASCUNHO => 'Rascunho',
            self::PUBLICADO => 'Publicado',
            self::CANCELADO => 'Cancelado',
        };
    }
}
