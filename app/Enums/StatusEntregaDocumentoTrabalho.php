<?php

declare(strict_types=1);

namespace App\Enums;

enum StatusEntregaDocumentoTrabalho: string
{
    case ENVIADA = 'enviada';
    case EM_AVALIACAO = 'em_avaliacao';
    case AVALIADA = 'avaliada';
    case DEVOLVIDA = 'devolvida';

    public function rotulo(): string
    {
        return match ($this) {
            self::ENVIADA => 'Enviada',
            self::EM_AVALIACAO => 'Em avaliação',
            self::AVALIADA => 'Avaliada',
            self::DEVOLVIDA => 'Devolvida',
        };
    }
}
