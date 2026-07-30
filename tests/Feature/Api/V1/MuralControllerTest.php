<?php

namespace Tests\Feature\Api\V1;

use App\Models\MuralComentario;
use App\Models\MuralPublicacao;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MuralControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_only_published_public_posts(): void
    {
        MuralPublicacao::factory()->publicado()->publico()->create(['titulo' => 'Publicação pública']);
        MuralPublicacao::factory()->publicado()->create(['titulo' => 'Publicação restrita']);

        $this->getJson(route('api.v1.mural.index'))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.titulo', 'Publicação pública');
    }

    public function test_comment_requires_a_token(): void
    {
        $publicacao = MuralPublicacao::factory()->publicado()->publico()->create();

        $this->postJson(route('api.v1.mural.comentarios.store', $publicacao), ['comentario' => 'Olá!'])
            ->assertUnauthorized();
    }

    /**
     * Exemplo obrigatório (adaptado): comentário de usuário sem permissão de
     * moderação entra pendente de aprovação, mesmo vindo do app.
     */
    public function test_comment_from_user_without_moderation_permission_is_pending(): void
    {
        $publicacao = MuralPublicacao::factory()->publicado()->publico()->create();
        $usuario = User::factory()->create();

        $this->actingAs($usuario, 'sanctum')
            ->postJson(route('api.v1.mural.comentarios.store', $publicacao), ['comentario' => 'Comentário via app.'])
            ->assertCreated();

        $comentario = MuralComentario::where('comentario', 'Comentário via app.')->firstOrFail();
        $this->assertFalse($comentario->aprovado);
    }

    public function test_reacting_twice_with_the_same_type_does_not_duplicate(): void
    {
        $publicacao = MuralPublicacao::factory()->publicado()->publico()->create();
        $usuario = User::factory()->create();

        $this->actingAs($usuario, 'sanctum')
            ->postJson(route('api.v1.mural.reacoes.store', $publicacao), ['tipo' => 'curtir'])
            ->assertCreated();

        $this->actingAs($usuario, 'sanctum')
            ->postJson(route('api.v1.mural.reacoes.store', $publicacao), ['tipo' => 'curtir'])
            ->assertCreated();

        $this->assertSame(1, $publicacao->reacoes()->count());
    }
}
