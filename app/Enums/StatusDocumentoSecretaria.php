<?php

declare(strict_types=1);

namespace App\Enums;

enum StatusDocumentoSecretaria: string
{
    case RASCUNHO = 'rascunho';
    case EM_APROVACAO = 'em_aprovacao';
    case APROVADO = 'aprovado';
    case PUBLICADO = 'publicado';
    case ARQUIVADO = 'arquivado';

    public function rotulo(): string
    {
        return match ($this) {
            self::RASCUNHO => 'Rascunho',
            self::EM_APROVACAO => 'Em aprovação',
            self::APROVADO => 'Aprovado',
            self::PUBLICADO => 'Publicado',
            self::ARQUIVADO => 'Arquivado',
        };
    }
}
