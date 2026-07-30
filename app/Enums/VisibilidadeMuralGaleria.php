<?php

declare(strict_types=1);

namespace App\Enums;

enum VisibilidadeMuralGaleria: string
{
    case PUBLICA = 'publica';
    case RESTRITA = 'restrita';

    public function rotulo(): string
    {
        return match ($this) {
            self::PUBLICA => 'Pública',
            self::RESTRITA => 'Restrita',
        };
    }
}
