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
}
