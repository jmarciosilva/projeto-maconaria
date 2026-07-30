<?php

namespace Tests\Unit;

use App\Enums\StatusEvento;
use App\Models\Evento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventoTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_accepts_confirmation_only_when_published_future_and_enabled(): void
    {
        $evento = Evento::factory()->publicado()->comConfirmacao()->create([
            'inicio_em' => now()->addDays(5),
            'inscricoes_ate' => now()->addDays(3),
        ]);

        $rascunho = Evento::factory()->comConfirmacao()->create([
            'status' => StatusEvento::RASCUNHO,
            'inicio_em' => now()->addDays(5),
            'inscricoes_ate' => now()->addDays(3),
        ]);

        $this->assertTrue($evento->aceitaConfirmacao());
        $this->assertFalse($rascunho->aceitaConfirmacao());
    }
}
