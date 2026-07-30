<?php

declare(strict_types=1);

namespace App\Enums;

enum CriptografiaEmail: string
{
    case TLS = 'tls';
    case SSL = 'ssl';

    public function rotulo(): string
    {
        return match ($this) {
            self::TLS => 'TLS',
            self::SSL => 'SSL',
        };
    }
}
