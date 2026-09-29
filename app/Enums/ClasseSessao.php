<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Classe da sessão da Loja. Complementa TipoEvento::SESSAO, que apenas
 * distingue sessão de evento comum na agenda.
 */
enum ClasseSessao: string
{
    case ORDINARIA = 'ordinaria';
    case MAGNA = 'magna';
    case ADMINISTRATIVA = 'administrativa';
    case ESPECIAL = 'especial';

    public function rotulo(): string
    {
        return match ($this) {
            self::ORDINARIA => 'Ordinária',
            self::MAGNA => 'Magna',
            self::ADMINISTRATIVA => 'Administrativa',
            self::ESPECIAL => 'Especial',
        };
    }
}
