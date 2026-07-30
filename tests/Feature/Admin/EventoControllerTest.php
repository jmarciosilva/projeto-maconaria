<?php

namespace Tests\Feature\Admin;

use App\Enums\StatusEvento;
use App\Enums\TipoEvento;
use App\Enums\VisibilidadeEvento;
use App\Models\Evento;
use App\Models\User;
use Database\Seeders\PerfilPermissaoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventoControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_permission_cannot_view_events_admin(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();

        $this->actingAs($usuario)
            ->get(route('admin.eventos.index'))
            ->assertForbidden();
    }

    public function test_user_with_permission_can_create_event(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('eventos.criar', 'eventos.visualizar');

        $this->actingAs($usuario)->post(route('admin.eventos.store'), [
            'titulo' => 'Palestra Aberta',
            'slug' => 'palestra-aberta',
            'tipo' => TipoEvento::EVENTO->value,
            'status' => StatusEvento::PUBLICADO->value,
            'visibilidade' => VisibilidadeEvento::PUBLICA->value,
            'inicio_em' => now()->addWeek()->format('Y-m-d H:i:s'),
            'fim_em' => now()->addWeek()->addHours(2)->format('Y-m-d H:i:s'),
        ])->assertRedirect();

        $this->assertDatabaseHas('eventos', [
            'slug' => 'palestra-aberta',
            'status' => StatusEvento::PUBLICADO->value,
        ]);
        $this->assertDatabaseHas('auditorias', ['modulo' => 'eventos', 'entidade' => 'Evento']);
    }

    public function test_event_slug_must_be_unique(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('eventos.criar');

        Evento::factory()->create(['slug' => 'evento-repetido']);

        $this->actingAs($usuario)->post(route('admin.eventos.store'), [
            'titulo' => 'Outro evento',
            'slug' => 'evento-repetido',
            'tipo' => TipoEvento::EVENTO->value,
            'status' => StatusEvento::RASCUNHO->value,
            'visibilidade' => VisibilidadeEvento::PUBLICA->value,
            'inicio_em' => now()->addWeek()->format('Y-m-d H:i:s'),
        ])->assertSessionHasErrors('slug');
    }
}
