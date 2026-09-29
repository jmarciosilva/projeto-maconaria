<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Garante que a configuração VERSIONADA (a que entra na imagem pelo deploy)
 * comporta as 50 fotografias por notícia permitidas pelo Laravel.
 *
 * O default do PHP para max_file_uploads é 20, o que truncava silenciosamente
 * os uploads antes de o Laravel enxergá-los.
 */
class LimitesUploadFotosTest extends TestCase
{
    private const MAX_FOTOS_LARAVEL = 50;

    private function php(): string
    {
        return file_get_contents(__DIR__.'/../../deploy/php.ini');
    }

    private function nginx(): string
    {
        return file_get_contents(__DIR__.'/../../deploy/nginx.conf');
    }

    private function diretiva(string $conteudo, string $padrao): string
    {
        $this->assertMatchesRegularExpression($padrao, $conteudo);
        preg_match($padrao, $conteudo, $m);

        return $m[1];
    }

    private function paraBytes(string $valor): int
    {
        $valor = trim($valor);
        $unidade = strtoupper(substr($valor, -1));
        $numero = (int) $valor;

        return match ($unidade) {
            'G' => $numero * 1024 * 1024 * 1024,
            'M' => $numero * 1024 * 1024,
            'K' => $numero * 1024,
            default => $numero,
        };
    }

    public function test_php_ini_define_max_file_uploads_suficiente_para_50_fotos(): void
    {
        $valor = (int) $this->diretiva($this->php(), '/^\s*max_file_uploads\s*=\s*(\d+)/m');

        // 50 fotos + imagem_capa + margem para campos de arquivo futuros
        $this->assertSame(60, $valor);
        $this->assertGreaterThan(self::MAX_FOTOS_LARAVEL, $valor);
    }

    public function test_post_max_size_e_coerente_com_upload_max_filesize(): void
    {
        $conteudo = $this->php();

        $post = $this->paraBytes($this->diretiva($conteudo, '/^\s*post_max_size\s*=\s*(\S+)/m'));
        $upload = $this->paraBytes($this->diretiva($conteudo, '/^\s*upload_max_filesize\s*=\s*(\S+)/m'));

        $this->assertSame(128 * 1024 * 1024, $post);
        $this->assertSame(64 * 1024 * 1024, $upload);

        // O PHP só aceita um arquivo até o menor entre os dois limites.
        $this->assertGreaterThanOrEqual($upload, $post);
    }

    public function test_post_max_size_comporta_um_lote_realista_de_50_fotos(): void
    {
        $post = $this->paraBytes($this->diretiva($this->php(), '/^\s*post_max_size\s*=\s*(\S+)/m'));

        // Tamanho real medido em produção: média ~188 KB, maior ~297 KB.
        // Mesmo assumindo 300 KB por foto, 50 fotos cabem com folga larga.
        $loteRealista = self::MAX_FOTOS_LARAVEL * 300 * 1024;

        $this->assertGreaterThan($loteRealista * 2, $post);
    }

    public function test_nginx_do_container_nao_corta_antes_do_php(): void
    {
        $post = $this->paraBytes($this->diretiva($this->php(), '/^\s*post_max_size\s*=\s*(\S+)/m'));
        $body = $this->paraBytes($this->diretiva($this->nginx(), '/^\s*client_max_body_size\s+(\S+?);/m'));

        $this->assertGreaterThanOrEqual($post, $body);
    }
}
