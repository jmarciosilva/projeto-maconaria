<?php

declare(strict_types=1);

namespace App\Support;

final class NormalizadorTexto
{
    public static function paraUtf8(?string $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        if (! mb_check_encoding($valor, 'UTF-8')) {
            $valor = mb_convert_encoding($valor, 'UTF-8', 'Windows-1252');
        }

        return self::corrigirMojibake($valor);
    }

    private static function corrigirMojibake(string $valor): string
    {
        $pontuacaoOriginal = self::pontuacaoMojibake($valor);

        if ($pontuacaoOriginal === 0) {
            return $valor;
        }

        $bytesOriginais = @iconv('UTF-8', 'Windows-1252//IGNORE', $valor);

        if ($bytesOriginais === false) {
            return $valor;
        }

        $corrigido = $bytesOriginais;

        if ($corrigido === '' || ! mb_check_encoding($corrigido, 'UTF-8')) {
            return $valor;
        }

        return self::pontuacaoMojibake($corrigido) < $pontuacaoOriginal ? $corrigido : $valor;
    }

    private static function pontuacaoMojibake(string $valor): int
    {
        preg_match_all('/(?:Ã.|Â.|â€.|â€œ|â€|â€™|â€“|â€”|â€¢|ï¿½|�)/u', $valor, $ocorrencias);

        return count($ocorrencias[0]);
    }
}
