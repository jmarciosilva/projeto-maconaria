<?php

namespace Tests\Feature\Site;

use App\Models\MuralPublicacao;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MuralTest extends TestCase
{
    use RefreshDatabase;

    /**
     * O mural é sempre público para visualização — só curtir/comentar
     * exige login (ver test_reacting_requires_login).
     */
    public function test_public_post_appears_on_public_mural_page_without_login(): void
    {
        $publicacao = MuralPublicacao::factory()->publicado()->publico()->create(['titulo' => 'Comunicado da Loja']);

        $this->get(route('mural.index'))
            ->assertOk()
            ->assertSee($publicacao->titulo);
    }

    public function test_restricted_post_does_not_appear_on_public_mural(): void
    {
        $restrita = MuralPublicacao::factory()->publicado()->create(['titulo' => 'Publicação restrita']);

        $this->get(route('mural.index'))
            ->assertOk()
            ->assertDontSee($restrita->titulo);

        $this->get(route('mural.mostrar', $restrita))
            ->assertNotFound();
    }

    public function test_guest_cannot_react_and_is_invited_to_login(): void
    {
        $publicacao = MuralPublicacao::factory()->publicado()->publico()->create();

        $this->get(route('mural.mostrar', $publicacao))
            ->assertOk()
            ->assertSee(route('login'), false);

        $this->post(route('mural.reacoes.store', $publicacao), ['tipo' => 'curtir'])
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_react_from_the_public_post_page(): void
    {
        $publicacao = MuralPublicacao::factory()->publicado()->publico()->create();
        $usuario = User::factory()->create();

        $this->actingAs($usuario)
            ->post(route('mural.reacoes.store', $publicacao), ['tipo' => 'curtir'])
            ->assertRedirect();

        $this->assertDatabaseHas('mural_reacoes', [
            'publicacao_id' => $publicacao->id,
            'usuario_id' => $usuario->id,
        ]);
    }
}
