<?php

namespace Tests\Unit;

use App\Enums\StatusNoticia;
use App\Enums\VisibilidadeNoticia;
use App\Models\Noticia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoticiaTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_published_public_news_is_public_on_site(): void
    {
        $publica = Noticia::factory()->publicada()->create();
        $restrita = Noticia::factory()->publicada()->restrita()->create();
        $rascunho = Noticia::factory()->create(['status' => StatusNoticia::RASCUNHO]);

        $this->assertTrue($publica->estaPublicaNoSite());
        $this->assertFalse($restrita->estaPublicaNoSite());
        $this->assertFalse($rascunho->estaPublicaNoSite());
    }

    public function test_future_publication_is_not_public_on_site_yet(): void
    {
        $noticia = Noticia::factory()->create([
            'status' => StatusNoticia::PUBLICADA,
            'visibilidade' => VisibilidadeNoticia::PUBLICA,
            'publicado_em' => now()->addDay(),
        ]);

        $this->assertFalse($noticia->estaPublicaNoSite());
    }
}
