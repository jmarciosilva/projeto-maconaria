<?php

declare(strict_types=1);

namespace App\Enums;

enum TipoDocumentoSecretaria: string
{
    case ATA = 'ata';
    case CORRESPONDENCIA = 'correspondencia';
    case DOCUMENTO_OFICIAL = 'documento_oficial';

    public function rotulo(): string
    {
        return match ($this) {
            self::ATA => 'Ata',
            self::CORRESPONDENCIA => 'Correspondência',
            self::DOCUMENTO_OFICIAL => 'Documento oficial',
        };
    }

    public function prefixo(): string
    {
        return match ($this) {
            self::ATA => 'ATA',
            self::CORRESPONDENCIA => 'COR',
            self::DOCUMENTO_OFICIAL => 'DOC',
        };
    }
}
