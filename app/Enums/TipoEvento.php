<?php

declare(strict_types=1);

namespace App\Enums;

enum TipoEvento: string
{
    case EVENTO = 'evento';
    case SESSAO = 'sessao';

    public function rotulo(): string
    {
        return match ($this) {
            self::EVENTO => 'Evento',
            self::SESSAO => 'Sessão',
        };
    }
}
