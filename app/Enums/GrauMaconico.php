<?php

declare(strict_types=1);

namespace App\Enums;

enum GrauMaconico: string
{
    case APRENDIZ = 'aprendiz';
    case COMPANHEIRO = 'companheiro';
    case MESTRE = 'mestre';

    public function rotulo(): string
    {
        return match ($this) {
            self::APRENDIZ => 'Aprendiz',
            self::COMPANHEIRO => 'Companheiro',
            self::MESTRE => 'Mestre',
        };
    }
}
