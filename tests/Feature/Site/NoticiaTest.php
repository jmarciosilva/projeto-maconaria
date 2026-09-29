<?php

namespace Tests\Feature\Site;

use App\Models\Noticia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoticiaTest extends TestCase
{
    use RefreshDatabase;

    public function test_restricted_publication_does_not_appear_on_landing_page(): void
    {
        $publica = Noticia::factory()->publicada()->create([
            'titulo' => 'Notícia pública em destaque',
            'slug' => 'noticia-publica-em-destaque',
            'destaque' => true,
        ]);

        $restrita = Noticia::factory()->publicada()->restrita()->create([
            'titulo' => 'Notícia restrita em destaque',
            'slug' => 'noticia-restrita-em-destaque',
            'destaque' => true,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee($publica->titulo)
            ->assertDontSee($restrita->titulo);
    }

    public function test_restricted_publication_cannot_be_opened_publicly(): void
    {
        $restrita = Noticia::factory()->publicada()->restrita()->create([
            'slug' => 'noticia-restrita',
        ]);

        $this->get(route('noticias.mostrar', $restrita->slug))
            ->assertNotFound();
    }

    public function test_gallery_photos_are_shown_whole_and_not_cropped(): void
    {
        $noticia = Noticia::factory()->publicada()->create([
            'slug' => 'noticia-galeria-sem-corte',
        ]);

        for ($i = 0; $i < 3; $i++) {
            $noticia->fotos()->create([
                'caminho' => "noticias/fotos/inteira$i.jpg",
                'ordem' => $i,
            ]);
        }

        $html = $this->get(route('noticias.mostrar', $noticia->slug))
            ->assertOk()
            ->getContent();

        // Uma foto recortada usaria object-cover; a galeria deve exibir a
        // fotografia inteira (object-contain), uma ocorrência por foto.
        $this->assertSame(3, substr_count($html, 'h-full w-full object-contain object-center'));
        $this->assertStringNotContainsString('object-cover object-center', $html);
    }

    public function test_news_without_photos_has_no_gallery_image_styling(): void
    {
        $noticia = Noticia::factory()->publicada()->create([
            'slug' => 'noticia-galeria-vazia',
        ]);

        $html = $this->get(route('noticias.mostrar', $noticia->slug))
            ->assertOk()
            ->getContent();

        // Garante que a marcação medida acima vem da galeria, e não do layout
        // (o logotipo do site também usa object-contain em toda página).
        $this->assertSame(0, substr_count($html, 'h-full w-full object-contain object-center'));
    }

    public function test_public_detail_renders_content_before_photo_gallery(): void
    {
        $noticia = Noticia::factory()->publicada()->create([
            'slug' => 'noticia-ordem-conteudo-galeria',
            'conteudo' => '<p>MARCADOR_CONTEUDO_UNICO_DA_NOTICIA</p>',
        ]);

        for ($i = 0; $i < 3; $i++) {
            $noticia->fotos()->create([
                'caminho' => "noticias/fotos/ordem$i.jpg",
                'descricao' => $i === 0 ? 'Legenda opcional' : null,
                'ordem' => $i,
            ]);
        }

        $response = $this->get(route('noticias.mostrar', $noticia->slug));

        $response->assertOk();
        // F) conteúdo/texto da notícia é renderizado
        $response->assertSee('MARCADOR_CONTEUDO_UNICO_DA_NOTICIA', false);
        // G) galeria de fotografias é renderizada
        $response->assertSee('Fotografias');
        $response->assertSee('ordem0.jpg', false);
        // H) o conteúdo aparece ANTES da seção Fotografias no HTML
        $response->assertSeeInOrder([
            'MARCADOR_CONTEUDO_UNICO_DA_NOTICIA',
            'Fotografias',
        ], false);
    }
}
