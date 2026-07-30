<?php

namespace Tests\Feature\Site;

use App\Models\Evento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventoTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_event_appears_on_public_events_page(): void
    {
        $evento = Evento::factory()->publicado()->create([
            'titulo' => 'Palestra pública',
            'slug' => 'palestra-publica',
        ]);

        $this->get(route('eventos.index'))
            ->assertOk()
            ->assertSee($evento->titulo);
    }

    public function test_restricted_event_does_not_appear_publicly(): void
    {
        $restrito = Evento::factory()->publicado()->restrito()->create([
            'titulo' => 'Sessão restrita',
            'slug' => 'sessao-restrita',
        ]);

        $this->get(route('eventos.index'))
            ->assertOk()
            ->assertDontSee($restrito->titulo);

        $this->get(route('eventos.mostrar', $restrito->slug))
            ->assertNotFound();
    }

    public function test_public_event_appears_on_landing_page(): void
    {
        $evento = Evento::factory()->publicado()->create([
            'titulo' => 'Evento na home',
            'slug' => 'evento-na-home',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee($evento->titulo);
    }

    /**
     * O calendário público nunca exige login para visualizar — só a
     * confirmação de presença (feita na área restrita) exige.
     */
    public function test_public_calendar_never_requires_login(): void
    {
        $evento = Evento::factory()->publicado()->create([
            'titulo' => 'Sessão do mês',
            'inicio_em' => now()->addDays(3),
        ]);

        $this->get(route('calendario'))
            ->assertOk()
            ->assertSee($evento->titulo);
    }

    public function test_restricted_event_does_not_appear_on_public_calendar(): void
    {
        $restrito = Evento::factory()->publicado()->restrito()->create([
            'titulo' => 'Sessão restrita no calendário',
            'inicio_em' => now()->addDays(3),
        ]);

        $this->get(route('calendario'))
            ->assertOk()
            ->assertDontSee($restrito->titulo);
    }
}
