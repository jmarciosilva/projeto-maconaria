<?php

namespace Tests\Feature\AreaRestrita;

use App\Enums\StatusConfirmacaoPresenca;
use App\Models\Evento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventoTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_restricted_event(): void
    {
        $usuario = User::factory()->create();
        $evento = Evento::factory()->publicado()->restrito()->create([
            'titulo' => 'Sessão restrita',
        ]);

        $this->actingAs($usuario)
            ->get(route('area-restrita.eventos.index'))
            ->assertOk()
            ->assertSee($evento->titulo);
    }

    public function test_authenticated_user_can_confirm_and_cancel_presence(): void
    {
        $usuario = User::factory()->create();
        $evento = Evento::factory()->publicado()->comConfirmacao()->create([
            'inicio_em' => now()->addDays(5),
            'inscricoes_ate' => now()->addDays(3),
        ]);

        $this->actingAs($usuario)
            ->post(route('area-restrita.eventos.confirmar', $evento), [
                'observacao' => 'Chegarei no horário.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('evento_confirmacoes_presenca', [
            'evento_id' => $evento->id,
            'usuario_id' => $usuario->id,
            'status' => StatusConfirmacaoPresenca::CONFIRMADO->value,
        ]);

        $this->actingAs($usuario)
            ->delete(route('area-restrita.eventos.cancelar-confirmacao', $evento))
            ->assertRedirect();

        $this->assertDatabaseHas('evento_confirmacoes_presenca', [
            'evento_id' => $evento->id,
            'usuario_id' => $usuario->id,
            'status' => StatusConfirmacaoPresenca::CANCELADO->value,
        ]);
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

        $this->actingAs($primeiro)
            ->post(route('area-restrita.eventos.confirmar', $evento))
            ->assertRedirect();

        $this->actingAs($segundo)
            ->post(route('area-restrita.eventos.confirmar', $evento))
            ->assertStatus(422);

        $this->assertDatabaseMissing('evento_confirmacoes_presenca', [
            'evento_id' => $evento->id,
            'usuario_id' => $segundo->id,
        ]);
    }
}
