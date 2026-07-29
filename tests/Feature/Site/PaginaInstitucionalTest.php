<?php

namespace Tests\Feature\Site;

use App\Models\PaginaInstitucional;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaginaInstitucionalTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_page_is_visible(): void
    {
        PaginaInstitucional::factory()->create([
            'slug' => 'sobre-nos',
            'titulo' => 'Sobre Nós',
            'conteudo' => '<p>Conteúdo de teste</p>',
            'publicado' => true,
        ]);

        $this->get(route('paginas.sobre-nos'))
            ->assertOk()
            ->assertSee('Sobre Nós')
            ->assertSee('Conteúdo de teste', false);
    }

    public function test_unpublished_page_returns_404(): void
    {
        PaginaInstitucional::factory()->create([
            'slug' => 'sobre-nos',
            'publicado' => false,
        ]);

        $this->get(route('paginas.sobre-nos'))->assertNotFound();
    }

    public function test_meta_description_is_rendered_when_present(): void
    {
        PaginaInstitucional::factory()->create([
            'slug' => 'maconaria',
            'titulo' => 'O que é Maçonaria',
            'meta_descricao' => 'Descrição de SEO para teste',
            'publicado' => true,
        ]);

        $this->get(route('paginas.maconaria'))
            ->assertOk()
            ->assertSee('Descrição de SEO para teste', false);
    }

    public function test_generic_route_resolves_arbitrary_page_by_slug(): void
    {
        PaginaInstitucional::factory()->create([
            'slug' => 'pagina-livre',
            'titulo' => 'Página Livre',
            'publicado' => true,
        ]);

        $this->get(route('paginas.mostrar', ['slug' => 'pagina-livre']))
            ->assertOk()
            ->assertSee('Página Livre');
    }

    public function test_site_menu_shows_only_existing_published_institutional_pages(): void
    {
        PaginaInstitucional::factory()->create([
            'slug' => 'pagina-criada',
            'titulo' => 'Página Criada',
            'publicado' => true,
        ]);

        PaginaInstitucional::factory()->create([
            'slug' => 'mudar-cidadao',
            'titulo' => 'Como a Maçonaria pode mudar um cidadão',
            'publicado' => false,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Página Criada')
            ->assertSee(route('paginas.mostrar', 'pagina-criada'), false)
            ->assertDontSee('Como a Maçonaria pode mudar um cidadão');
    }
}
