<?php

declare(strict_types=1);

namespace App\Enums;

enum StatusMuralGaleria: string
{
    case RASCUNHO = 'rascunho';
    case PUBLICADO = 'publicado';
    case OCULTO = 'oculto';
    case REPROVADO = 'reprovado';

    public function rotulo(): string
    {
        return match ($this) {
            self::RASCUNHO => 'Rascunho',
            self::PUBLICADO => 'Publicado',
            self::OCULTO => 'Oculto',
            self::REPROVADO => 'Reprovado',
        };
    }
}
