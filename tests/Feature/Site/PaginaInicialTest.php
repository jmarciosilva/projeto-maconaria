<?php

namespace Tests\Feature\Site;

use App\Models\CarrosselItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaginaInicialTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_shows_fallback_hero_when_no_carrossel_items(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Augusta e Respeitável Loja Simbólica Ferraz de Vasconcelos');
    }

    public function test_home_page_shows_only_active_and_vigente_carrossel_items(): void
    {
        $ativo = CarrosselItem::factory()->create([
            'titulo' => 'Slide Ativo',
            'ativo' => true,
        ]);

        $inativo = CarrosselItem::factory()->create([
            'titulo' => 'Slide Inativo',
            'ativo' => false,
        ]);

        $expirado = CarrosselItem::factory()->create([
            'titulo' => 'Slide Expirado',
            'ativo' => true,
            'data_fim' => now()->subDay(),
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Slide Ativo');
        $response->assertDontSee('Slide Inativo');
        $response->assertDontSee('Slide Expirado');
    }
}
