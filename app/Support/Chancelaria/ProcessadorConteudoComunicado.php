<?php

declare(strict_types=1);

namespace App\Support\Chancelaria;

final class ProcessadorConteudoComunicado
{
    public static function prepararParaSalvar(?string $conteudo): string
    {
        return clean($conteudo ?? '', 'institucional');
    }
}
