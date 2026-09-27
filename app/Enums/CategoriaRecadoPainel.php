<?php

declare(strict_types=1);

namespace App\Enums;

enum CategoriaRecadoPainel: string
{
    case VENERAVEL = 'veneravel';
    case SECRETARIO = 'secretario';
    case CHANCELARIA = 'chancelaria';
    case TESOURARIA = 'tesouraria';
    case GERAL = 'geral';

    public function rotulo(): string
    {
        return match ($this) {
            self::VENERAVEL => 'Recado do Venerável Mestre',
            self::SECRETARIO => 'Recado do Secretário',
            self::CHANCELARIA => 'Recado da Chancelaria',
            self::TESOURARIA => 'Aviso da Tesouraria',
            self::GERAL => 'Aviso Geral',
        };
    }

    public function cor(): string
    {
        return match ($this) {
            self::VENERAVEL => 'roxo',
            self::SECRETARIO => 'azul',
            self::CHANCELARIA => 'verde',
            self::TESOURARIA => 'ambar',
            self::GERAL => 'cinza',
        };
    }
}
