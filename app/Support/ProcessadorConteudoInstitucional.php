<?php

declare(strict_types=1);

namespace App\Support;

use DOMDocument;
use DOMElement;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class ProcessadorConteudoInstitucional
{
    /**
     * Converte imagens coladas no editor como data URI em arquivos públicos.
     */
    public static function prepararParaSalvar(?string $conteudo): string
    {
        $conteudo = $conteudo ?? '';

        if (! str_contains($conteudo, 'data:image/')) {
            return clean($conteudo, 'institucional');
        }

        return clean(self::armazenarImagensBase64($conteudo), 'institucional');
    }

    private static function armazenarImagensBase64(string $conteudo): string
    {
        $documento = new DOMDocument('1.0', 'UTF-8');

        libxml_use_internal_errors(true);
        $documento->loadHTML(
            '<?xml encoding="UTF-8"><!DOCTYPE html><html><body><div id="conteudo-institucional">'.$conteudo.'</div></body></html>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();

        $imagens = $documento->getElementsByTagName('img');

        foreach ($imagens as $imagem) {
            if (! $imagem instanceof DOMElement) {
                continue;
            }

            $src = $imagem->getAttribute('src');

            if (! str_starts_with($src, 'data:image/')) {
                continue;
            }

            $caminho = self::armazenarDataUri($src);

            if ($caminho === null) {
                $imagem->parentNode?->removeChild($imagem);

                continue;
            }

            $imagem->setAttribute('src', '/storage/'.$caminho);

            if (! $imagem->hasAttribute('alt')) {
                $imagem->setAttribute('alt', 'Imagem do conteúdo institucional');
            }
        }

        $container = $documento->getElementById('conteudo-institucional');

        if (! $container) {
            return $conteudo;
        }

        $html = '';

        foreach ($container->childNodes as $node) {
            $html .= $documento->saveHTML($node);
        }

        return $html;
    }

    private static function armazenarDataUri(string $src): ?string
    {
        if (! preg_match('/^data:image\/(png|jpeg|jpg|gif|webp);base64,(.+)$/i', $src, $matches)) {
            return null;
        }

        $extensao = strtolower($matches[1]) === 'jpeg' ? 'jpg' : strtolower($matches[1]);
        $binario = base64_decode($matches[2], true);

        if ($binario === false) {
            return null;
        }

        $mime = finfo_buffer(finfo_open(FILEINFO_MIME_TYPE), $binario);

        if (! in_array($mime, ['image/png', 'image/jpeg', 'image/gif', 'image/webp'], true)) {
            return null;
        }

        $caminho = 'paginas-institucionais/imagens/'.Str::uuid().'.'.$extensao;

        Storage::disk('public')->put($caminho, $binario);

        return $caminho;
    }
}
