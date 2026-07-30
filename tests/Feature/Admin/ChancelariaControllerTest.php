<?php

namespace Tests\Feature\Admin;

use App\Enums\StatusComunicadoChancelaria;
use App\Enums\StatusFrequencia;
use App\Models\ChancelariaComunicado;
use App\Models\ChancelariaFrequencia;
use App\Models\ChancelariaVisitante;
use App\Models\Evento;
use App\Models\Irmao;
use App\Models\User;
use Database\Seeders\PerfilPermissaoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChancelariaControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_permission_cannot_view_chancelaria(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();

        $this->actingAs($usuario)
            ->get(route('admin.chancelaria.index'))
            ->assertForbidden();
    }

    public function test_user_with_permission_can_record_presence_and_absence(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('chancelaria.editar');
        $evento = Evento::factory()->publicado()->create();
        $presente = Irmao::factory()->create(['nome_completo' => 'Irmão Presente']);
        $ausente = Irmao::factory()->create(['nome_completo' => 'Irmão Ausente']);

        $this->actingAs($usuario)->put(route('admin.chancelaria.frequencias.update', $evento), [
            'frequencias' => [
                $presente->id => ['status' => StatusFrequencia::PRESENTE->value, 'observacao' => null],
                $ausente->id => ['status' => StatusFrequencia::JUSTIFICADO->value, 'observacao' => 'Viagem informada.'],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('chancelaria_frequencias', [
            'evento_id' => $evento->id,
            'irmao_id' => $presente->id,
            'status' => StatusFrequencia::PRESENTE->value,
        ]);
        $this->assertDatabaseHas('chancelaria_frequencias', [
            'evento_id' => $evento->id,
            'irmao_id' => $ausente->id,
            'status' => StatusFrequencia::JUSTIFICADO->value,
        ]);
        $this->assertDatabaseHas('auditorias', ['modulo' => 'chancelaria', 'acao' => 'registrar-frequencia']);
    }

    public function test_user_can_register_visitor(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('chancelaria.criar');
        $evento = Evento::factory()->publicado()->create();

        $this->actingAs($usuario)->post(route('admin.chancelaria.visitantes.store'), [
            'evento_id' => $evento->id,
            'nome' => 'Visitante Externo',
            'loja_origem' => 'Loja União',
            'potencia' => 'GOB',
        ])->assertRedirect();

        $this->assertDatabaseHas('chancelaria_visitantes', [
            'evento_id' => $evento->id,
            'nome' => 'Visitante Externo',
        ]);
    }

    public function test_comunicado_content_is_sanitized_and_can_be_published(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('chancelaria.criar');

        $this->actingAs($usuario)->post(route('admin.chancelaria.comunicados.store'), [
            'titulo' => 'Comunicado de teste',
            'conteudo' => '<p>Texto seguro</p><script>alert(1)</script><a href="javascript:alert(1)">link</a>',
            'status' => StatusComunicadoChancelaria::PUBLICADO->value,
        ])->assertRedirect();

        $comunicado = ChancelariaComunicado::where('titulo', 'Comunicado de teste')->firstOrFail();

        $this->assertSame(StatusComunicadoChancelaria::PUBLICADO, $comunicado->status);
        $this->assertNotNull($comunicado->publicado_em);
        $this->assertStringContainsString('Texto seguro', $comunicado->conteudo);
        $this->assertStringNotContainsString('<script', $comunicado->conteudo);
        $this->assertStringNotContainsString('javascript:', $comunicado->conteudo);
    }

    public function test_dashboard_displays_frequency_and_visitors(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('chancelaria.visualizar');
        $evento = Evento::factory()->publicado()->create(['titulo' => 'Sessão no painel']);
        $irmao = Irmao::factory()->create();
        ChancelariaFrequencia::create([
            'evento_id' => $evento->id,
            'irmao_id' => $irmao->id,
            'status' => StatusFrequencia::PRESENTE,
        ]);
        ChancelariaVisitante::factory()->create([
            'evento_id' => $evento->id,
            'nome' => 'Visitante no painel',
        ]);

        $this->actingAs($usuario)
            ->get(route('admin.chancelaria.index'))
            ->assertOk()
            ->assertSee('Sessão no painel')
            ->assertSee('Visitante no painel');
    }
}
