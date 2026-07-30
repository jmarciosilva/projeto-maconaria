<?php

declare(strict_types=1);

namespace App\Enums;

enum TipoReacaoMural: string
{
    case CURTIR = 'curtir';
    case APOIAR = 'apoiar';
    case CELEBRAR = 'celebrar';

    public function rotulo(): string
    {
        return match ($this) {
            self::CURTIR => 'Curtir',
            self::APOIAR => 'Apoiar',
            self::CELEBRAR => 'Celebrar',
        };
    }
}
