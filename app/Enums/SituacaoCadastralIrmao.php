<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Os valores exatos de situação cadastral não foram detalhados no escopo
 * original. Este conjunto é uma suposição conservadora que cobre os casos
 * mais comuns de administração de uma Loja — ver docs/MODULOS.md.
 */
enum SituacaoCadastralIrmao: string
{
    case ATIVO = 'ativo';
    case INATIVO = 'inativo';
    case LICENCIADO = 'licenciado';
    case IRREGULAR = 'irregular';
    case DESLIGADO = 'desligado';
    case FALECIDO = 'falecido';

    public function rotulo(): string
    {
        return match ($this) {
            self::ATIVO => 'Ativo',
            self::INATIVO => 'Inativo',
            self::LICENCIADO => 'Licenciado',
            self::IRREGULAR => 'Irregular',
            self::DESLIGADO => 'Desligado',
            self::FALECIDO => 'Falecido',
        };
    }
}
