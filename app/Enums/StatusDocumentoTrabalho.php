<?php

declare(strict_types=1);

namespace App\Enums;

enum StatusDocumentoTrabalho: string
{
    case RASCUNHO = 'rascunho';
    case PUBLICADA = 'publicada';
    case ENCERRADA = 'encerrada';

    public function rotulo(): string
    {
        return match ($this) {
            self::RASCUNHO => 'Rascunho',
            self::PUBLICADA => 'Publicada',
            self::ENCERRADA => 'Encerrada',
        };
    }
}
