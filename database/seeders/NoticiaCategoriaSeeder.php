<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\NoticiaCategoria;
use Illuminate\Database\Seeder;

/**
 * Categorias de notícias voltadas ao público externo do site (visitantes e
 * comunidade), evitando qualquer conteúdo iniciático ou de trabalhos
 * internos da Loja.
 */
final class NoticiaCategoriaSeeder extends Seeder
{
    /**
     * @var array<int, array{nome: string, slug: string, descricao: string}>
     */
    private const CATEGORIAS = [
        [
            'nome' => 'Eventos e Sessões Públicas',
            'slug' => 'eventos-publicos',
            'descricao' => 'Sessões magnas abertas, posses e comemorações que recebem visitantes.',
        ],
        [
            'nome' => 'Ação Social e Beneficência',
            'slug' => 'acao-social',
            'descricao' => 'Campanhas, doações e parcerias com instituições da comunidade.',
        ],
        [
            'nome' => 'Institucional',
            'slug' => 'institucional',
            'descricao' => 'Comunicados públicos da Loja: aniversários, posses e novos membros.',
        ],
        [
            'nome' => 'Cultura e Educação Maçônica',
            'slug' => 'cultura-maconica',
            'descricao' => 'Artigos introdutórios sobre história, filosofia e símbolos da maçonaria.',
        ],
        [
            'nome' => 'Imprensa e Divulgação',
            'slug' => 'imprensa',
            'descricao' => 'Matérias sobre a Loja publicadas em outros veículos e releases próprios.',
        ],
        [
            'nome' => 'Efemérides e Datas Comemorativas',
            'slug' => 'efemerides',
            'descricao' => 'Datas cívicas e comemorativas em que a Loja participa ou se manifesta.',
        ],
        [
            'nome' => 'Palestras e Eventos Abertos ao Público',
            'slug' => 'palestras-publicas',
            'descricao' => 'Palestras e encontros temáticos promovidos pela Loja e abertos à comunidade.',
        ],
    ];

    public function run(): void
    {
        foreach (self::CATEGORIAS as $categoria) {
            NoticiaCategoria::firstOrCreate(
                ['slug' => $categoria['slug']],
                [
                    'nome' => $categoria['nome'],
                    'descricao' => $categoria['descricao'],
                    'ativa' => true,
                ],
            );
        }
    }
}
