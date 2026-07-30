<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\StatusNoticia;
use App\Enums\VisibilidadeNoticia;
use App\Models\Noticia;
use App\Models\NoticiaCategoria;
use App\Models\NoticiaTag;
use App\Models\User;
use Illuminate\Database\Seeder;

final class NoticiaSeeder extends Seeder
{
    public function run(): void
    {
        $autor = User::query()->first();

        if (! $autor) {
            return;
        }

        $categoria = NoticiaCategoria::firstOrCreate(
            ['slug' => 'institucional'],
            ['nome' => 'Institucional', 'descricao' => 'Comunicados públicos da Loja.', 'ativa' => true],
        );

        $tag = NoticiaTag::firstOrCreate(
            ['slug' => 'comunicado'],
            ['nome' => 'Comunicado'],
        );

        $noticia = Noticia::firstOrCreate(
            ['slug' => 'bem-vindo-ao-novo-portal'],
            [
                'categoria_id' => $categoria->id,
                'autor_id' => $autor->id,
                'titulo' => 'Bem-vindo ao novo portal',
                'resumo' => 'Acompanhe comunicados, notícias e conteúdos institucionais publicados pela Loja.',
                'conteudo' => '<p>Este espaço reunirá as principais notícias públicas da Loja.</p>',
                'status' => StatusNoticia::PUBLICADA,
                'visibilidade' => VisibilidadeNoticia::PUBLICA,
                'destaque' => true,
                'publicado_em' => now(),
            ],
        );

        $noticia->tags()->syncWithoutDetaching([$tag->id]);
    }
}
