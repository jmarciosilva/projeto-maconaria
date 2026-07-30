<?php

namespace Tests\Feature\Api\V1;

use App\Models\Noticia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoticiaControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_only_published_public_news(): void
    {
        Noticia::factory()->publicada()->create(['titulo' => 'Notícia pública']);
        Noticia::factory()->restrita()->publicada()->create(['titulo' => 'Notícia restrita']);
        Noticia::factory()->create(['titulo' => 'Notícia em rascunho']);

        $this->getJson(route('api.v1.noticias.index'))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.titulo', 'Notícia pública');
    }

    /**
     * Notícias restritas não têm hoje nenhuma superfície fora do painel
     * administrativo (nem para usuários autenticados) — a API não inventa
     * esse comportamento. Ver docs/API-FUTURA.md.
     */
    public function test_restricted_news_is_not_visible_even_to_authenticated_users(): void
    {
        $noticia = Noticia::factory()->restrita()->publicada()->create(['slug' => 'noticia-restrita']);
        $usuario = User::factory()->create();

        $this->actingAs($usuario, 'sanctum')
            ->getJson(route('api.v1.noticias.show', $noticia->slug))
            ->assertNotFound();
    }

    public function test_show_returns_404_for_unknown_slug(): void
    {
        $this->getJson(route('api.v1.noticias.show', 'nao-existe'))->assertNotFound();
    }

    public function test_show_returns_full_content_for_public_news(): void
    {
        $noticia = Noticia::factory()->publicada()->create([
            'slug' => 'noticia-de-teste',
            'conteudo' => '<p>Conteúdo completo.</p>',
        ]);

        $this->getJson(route('api.v1.noticias.show', $noticia->slug))
            ->assertOk()
            ->assertJsonPath('slug', 'noticia-de-teste')
            ->assertJsonPath('conteudo', '<p>Conteúdo completo.</p>');
    }
}
