<?php

declare(strict_types=1);

namespace App\Support;

final class ProcessadorConteudoNoticia
{
    public static function prepararParaSalvar(?string $conteudo): string
    {
        return clean($conteudo ?? '', 'institucional');
    }
}
