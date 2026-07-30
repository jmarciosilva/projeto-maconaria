<?php

namespace Tests\Feature\Api\V1;

use App\Enums\StatusConfirmacaoPresenca;
use App\Models\Evento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventoControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_only_sees_public_events(): void
    {
        Evento::factory()->publicado()->create(['titulo' => 'Evento público']);
        Evento::factory()->publicado()->restrito()->create(['titulo' => 'Sessão restrita']);

        $this->getJson(route('api.v1.eventos.index'))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.titulo', 'Evento público');
    }

    public function test_authenticated_user_sees_restricted_events_too(): void
    {
        Evento::factory()->publicado()->create(['titulo' => 'Evento público']);
        Evento::factory()->publicado()->restrito()->create(['titulo' => 'Sessão restrita']);
        $usuario = User::factory()->create();

        $this->actingAs($usuario, 'sanctum')
            ->getJson(route('api.v1.eventos.index'))
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_authenticated_user_can_confirm_and_cancel_presence(): void
    {
        $usuario = User::factory()->create();
        $evento = Evento::factory()->publicado()->comConfirmacao()->create([
            'inicio_em' => now()->addDays(5),
            'inscricoes_ate' => now()->addDays(3),
        ]);

        $this->actingAs($usuario, 'sanctum')
            ->postJson(route('api.v1.eventos.confirmar', $evento), ['observacao' => 'Chegarei no horário.'])
            ->assertOk();

        $this->assertDatabaseHas('evento_confirmacoes_presenca', [
            'evento_id' => $evento->id,
            'usuario_id' => $usuario->id,
            'status' => StatusConfirmacaoPresenca::CONFIRMADO->value,
        ]);

        $this->actingAs($usuario, 'sanctum')
            ->deleteJson(route('api.v1.eventos.cancelar-confirmacao', $evento))
            ->assertOk();

        $this->assertDatabaseHas('evento_confirmacoes_presenca', [
            'evento_id' => $evento->id,
            'usuario_id' => $usuario->id,
            'status' => StatusConfirmacaoPresenca::CANCELADO->value,
        ]);
    }

    public function test_confirming_presence_requires_a_token(): void
    {
        $evento = Evento::factory()->publicado()->comConfirmacao()->create([
            'inicio_em' => now()->addDays(5),
            'inscricoes_ate' => now()->addDays(3),
        ]);

        $this->postJson(route('api.v1.eventos.confirmar', $evento))->assertUnauthorized();
    }

    public function test_capacity_blocks_extra_confirmation(): void
    {
        $primeiro = User::factory()->create();
        $segundo = User::factory()->create();
        $evento = Evento::factory()->publicado()->comConfirmacao()->create([
            'capacidade' => 1,
            'inicio_em' => now()->addDays(5),
            'inscricoes_ate' => now()->addDays(3),
        ]);

        $this->actingAs($primeiro, 'sanctum')
            ->postJson(route('api.v1.eventos.confirmar', $evento))
            ->assertOk();

        $this->actingAs($segundo, 'sanctum')
            ->postJson(route('api.v1.eventos.confirmar', $evento))
            ->assertStatus(422);
    }

    public function test_public_calendar_never_requires_a_token(): void
    {
        $evento = Evento::factory()->publicado()->create(['titulo' => 'Sessão do mês', 'inicio_em' => now()->addDays(3)]);
        $restrito = Evento::factory()->publicado()->restrito()->create(['titulo' => 'Sessão restrita', 'inicio_em' => now()->addDays(3)]);

        $resposta = $this->getJson(route('api.v1.calendario'))->assertOk();

        $dia = $evento->inicio_em->format('Y-m-d');
        $titulos = collect($resposta->json("dias.{$dia}"))->pluck('titulo');

        $this->assertTrue($titulos->contains($evento->titulo));
        $this->assertFalse($titulos->contains($restrito->titulo));
    }
}
