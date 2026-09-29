<?php

declare(strict_types=1);

namespace App\Support\Pdf;

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Renderiza uma view Blade como PDF usando o DomPDF.
 *
 * O DomPDF foi escolhido por ser PHP puro: não exige binário externo nem
 * headless browser, e as extensões de que precisa (dom, mbstring, gd) já
 * existem na imagem de produção. O wrapper barryvdh/laravel-dompdf não foi
 * usado porque sua versão atual declara illuminate/support ^9|^10|^11|^12,
 * incompatível com o Laravel 13 deste projeto.
 *
 * Limitações do engine que as views de PDF precisam respeitar: não há
 * flexbox, grid, variáveis CSS nem @media. O layout se faz com tabelas,
 * bordas e padding.
 */
final class GeradorPdf
{
    /**
     * Fonte embutida no próprio DomPDF: cobre a acentuação portuguesa e o
     * símbolo de atenção sem depender de rede nem de fonte do sistema.
     */
    private const FONTE = 'DejaVu Sans';

    /**
     * @param  array<string, mixed>  $dados
     * @param  'portrait'|'landscape'  $orientacao
     * @param  string|null  $rodape  texto discreto repetido no pé de cada página
     * @return string bytes do PDF
     */
    public static function deView(
        string $view,
        array $dados,
        string $orientacao = 'portrait',
        ?string $rodape = null,
        bool $numerarPaginas = true,
    ): string {
        $dompdf = new Dompdf(self::opcoes());
        $dompdf->loadHtml(view($view, $dados)->render(), 'UTF-8');
        $dompdf->setPaper('a4', $orientacao);
        $dompdf->render();

        self::aplicarRodape($dompdf, $rodape, $numerarPaginas);

        return (string) $dompdf->output();
    }

    private static function opcoes(): Options
    {
        $options = new Options;

        $options->set('defaultFont', self::FONTE);
        $options->set('isHtml5ParserEnabled', true);

        // Nenhum recurso externo: o documento é montado só com o que já
        // existe no disco da aplicação.
        $options->set('isRemoteEnabled', false);

        // Execução de PHP dentro do template fica desligada (é o padrão, mas
        // convém ser explícito: a view do relatório não precisa disso e o
        // recurso amplia a superfície de ataque).
        $options->set('isPhpEnabled', false);

        // Restringe a leitura de arquivos locais às pastas de assets da
        // aplicação, para que um src="/etc/..." não seja resolvido.
        $options->set('chroot', [public_path(), storage_path('app/public')]);

        return $options;
    }

    /**
     * Rodapé e numeração ficam na margem inferior, desenhados sobre cada
     * página em vez de ocupar o fluxo. Isso repete a identificação do
     * documento em todas as folhas e devolve à área útil a altura que um
     * rodapé fluido consumiria.
     *
     * Canvas::page_text() é API documentada do DomPDF e resolve os
     * marcadores depois da paginação — não é hack de contador em CSS.
     */
    private static function aplicarRodape(Dompdf $dompdf, ?string $rodape, bool $numerarPaginas): void
    {
        if ($rodape === null && ! $numerarPaginas) {
            return;
        }

        $canvas = $dompdf->getCanvas();
        $fonte = $dompdf->getFontMetrics()->getFont(self::FONTE);
        $tamanho = 7.5;
        $cor = [0.35, 0.35, 0.35];
        $base = $canvas->get_height() - 26;

        if ($rodape !== null) {
            $canvas->page_text(x: 43, y: $base, text: $rodape, font: $fonte, size: $tamanho, color: $cor);
        }

        if ($numerarPaginas) {
            $texto = 'Página {PAGE_NUM} de {PAGE_COUNT}';

            $canvas->page_text(
                // Alinhado à direita da margem: o texto tem largura estável,
                // então medir a string basta para encostá-lo na margem.
                x: $canvas->get_width() - 43 - $dompdf->getFontMetrics()->getTextWidth($texto, $fonte, $tamanho),
                y: $base,
                text: $texto,
                font: $fonte,
                size: $tamanho,
                color: $cor,
            );
        }
    }
}
