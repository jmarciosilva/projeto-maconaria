<?php

declare(strict_types=1);

namespace App\Enums;

enum StatusComunicadoChancelaria: string
{
    case RASCUNHO = 'rascunho';
    case PUBLICADO = 'publicado';
    case ARQUIVADO = 'arquivado';

    public function rotulo(): string
    {
        return match ($this) {
            self::RASCUNHO => 'Rascunho',
            self::PUBLICADO => 'Publicado',
            self::ARQUIVADO => 'Arquivado',
        };
    }
}
