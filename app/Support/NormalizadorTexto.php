<?php

declare(strict_types=1);

namespace App\Support;

final class NormalizadorTexto
{
    /**
     * Partículas que permanecem em minúsculas quando não iniciam o nome.
     *
     * @var list<string>
     */
    private const PARTICULAS = ['da', 'das', 'de', 'do', 'dos', 'e'];

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

    /**
     * Normaliza um nome próprio para a capitalização usual em português.
     *
     * Colapsa espaços, rebaixa todo o texto para minúsculas e recapitaliza
     * cada palavra, inclusive após hífen e apóstrofo. As partículas listadas
     * em self::PARTICULAS ficam em minúsculas, exceto quando são a primeira
     * palavra do nome.
     *
     * Limitação consciente: por rebaixar o texto antes de recapitalizar, não
     * há suporte a intercaps. "McDonald" resulta em "Mcdonald". Tratar esse
     * caso exigiria um dicionário de exceções, deliberadamente fora de escopo.
     */
    public static function nomeProprio(?string $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        $valor = self::colapsarEspacos($valor);

        if ($valor === '') {
            return '';
        }

        $palavras = explode(' ', mb_strtolower($valor, 'UTF-8'));

        foreach ($palavras as $posicao => $palavra) {
            if ($posicao > 0 && in_array($palavra, self::PARTICULAS, true)) {
                continue;
            }

            $palavras[$posicao] = self::capitalizarSegmentos($palavra);
        }

        return implode(' ', $palavras);
    }

    /**
     * Remove espaços das pontas e reduz sequências internas a um único espaço.
     */
    private static function colapsarEspacos(string $valor): string
    {
        $valor = preg_replace('/[\s\x{00A0}]+/u', ' ', $valor) ?? $valor;

        return trim($valor);
    }

    /**
     * Coloca em maiúscula a primeira letra da palavra e a letra que segue
     * cada hífen ou apóstrofo, preservando a acentuação.
     */
    private static function capitalizarSegmentos(string $palavra): string
    {
        return preg_replace_callback(
            '/(?:^|(?<=[-\'’]))\p{L}/u',
            static fn (array $ocorrencia): string => mb_strtoupper($ocorrencia[0], 'UTF-8'),
            $palavra,
        ) ?? $palavra;
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
