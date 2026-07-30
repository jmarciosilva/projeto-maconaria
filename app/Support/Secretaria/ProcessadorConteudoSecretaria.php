<?php

declare(strict_types=1);

namespace App\Support\Secretaria;

final class ProcessadorConteudoSecretaria
{
    public static function prepararParaSalvar(?string $conteudo): string
    {
        return clean($conteudo ?? '', 'institucional');
    }
}
