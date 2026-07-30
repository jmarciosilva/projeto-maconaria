<?php

declare(strict_types=1);

namespace App\Enums;

enum MailerEmail: string
{
    case SMTP = 'smtp';
    case LOG = 'log';

    public function rotulo(): string
    {
        return match ($this) {
            self::SMTP => 'SMTP',
            self::LOG => 'Log (não envia de verdade — grava em arquivo, uso em desenvolvimento)',
        };
    }
}
