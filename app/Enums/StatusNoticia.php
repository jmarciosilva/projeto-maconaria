<?php

declare(strict_types=1);

namespace App\Enums;

enum StatusNoticia: string
{
    case RASCUNHO = 'rascunho';
    case AGENDADA = 'agendada';
    case PUBLICADA = 'publicada';
    case ARQUIVADA = 'arquivada';

    public function rotulo(): string
    {
        return match ($this) {
            self::RASCUNHO => 'Rascunho',
            self::AGENDADA => 'Agendada',
            self::PUBLICADA => 'Publicada',
            self::ARQUIVADA => 'Arquivada',
        };
    }
}
