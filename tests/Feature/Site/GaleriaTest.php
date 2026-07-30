<?php

namespace Tests\Feature\Site;

use App\Models\GaleriaAlbum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GaleriaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A galeria da Loja é sempre pública para visualização — nunca exige
     * login (só o painel administrativo, para gerenciar álbuns, exige).
     */
    public function test_public_album_appears_on_public_gallery_page_without_login(): void
    {
        $album = GaleriaAlbum::factory()->publicado()->publico()->create(['titulo' => 'Confraternização anual']);

        $this->get(route('galeria.index'))
            ->assertOk()
            ->assertSee($album->titulo);
    }

    public function test_restricted_album_does_not_appear_on_public_gallery(): void
    {
        $restrito = GaleriaAlbum::factory()->publicado()->create(['titulo' => 'Sessão restrita em fotos', 'slug' => 'sessao-restrita-fotos']);

        $this->get(route('galeria.index'))
            ->assertOk()
            ->assertDontSee($restrito->titulo);

        $this->get(route('galeria.mostrar', $restrito->slug))
            ->assertNotFound();
    }

    public function test_public_album_page_shows_its_photos(): void
    {
        $album = GaleriaAlbum::factory()->publicado()->publico()->create(['slug' => 'album-de-teste']);

        $this->get(route('galeria.mostrar', $album->slug))
            ->assertOk()
            ->assertSee($album->titulo);
    }
}
