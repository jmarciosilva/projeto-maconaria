<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PaginaInstitucional;
use Illuminate\Database\Seeder;

/**
 * Semeia as páginas institucionais fixas do site público (seção 5 e 7 do
 * escopo), com conteúdo placeholder. O texto real deve ser preenchido pelo
 * painel administrativo (admin/paginas-institucionais) por quem for
 * responsável pelo conteúdo da Loja.
 */
final class PaginaInstitucionalSeeder extends Seeder
{
    /**
     * @var array<int, array<string, string>>
     */
    private const PAGINAS = [
        [
            'slug' => 'sobre-nos',
            'titulo' => 'Sobre Nós',
        ],
        [
            'slug' => 'maconaria',
            'titulo' => 'O que é Maçonaria',
        ],
        [
            'slug' => 'maconaria-jovens',
            'titulo' => 'Maçonaria para Jovens',
        ],
        [
            'slug' => 'mudar-cidadao',
            'titulo' => 'Como a Maçonaria pode mudar um cidadão',
        ],
        [
            'slug' => 'politica-privacidade',
            'titulo' => 'Política de Privacidade',
        ],
        [
            'slug' => 'termos-de-uso',
            'titulo' => 'Termos de Uso',
        ],
    ];

    public function run(): void
    {
        foreach (self::PAGINAS as $pagina) {
            PaginaInstitucional::query()->firstOrCreate(
                ['slug' => $pagina['slug']],
                [
                    'titulo' => $pagina['titulo'],
                    'conteudo' => '<p>Conteúdo a ser definido pela administração da Loja.</p>',
                    'publicado' => true,
                ]
            );
        }
    }
}
