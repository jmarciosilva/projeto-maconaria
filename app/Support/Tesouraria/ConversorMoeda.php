<?php

declare(strict_types=1);

namespace App\Support\Tesouraria;

final class ConversorMoeda
{
    public static function paraCentavos(string|int|null $valor): int
    {
        $normalizado = str_replace(['.', ','], ['', '.'], (string) $valor);

        return (int) round(((float) $normalizado) * 100);
    }

    public static function formatar(int $centavos): string
    {
        return number_format($centavos / 100, 2, ',', '.');
    }
}
