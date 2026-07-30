<?php

namespace Tests\Feature\Api\V1;

use App\Models\Irmao;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerfilControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_own_profile(): void
    {
        $usuario = User::factory()->create();

        $this->actingAs($usuario, 'sanctum')
            ->getJson(route('api.v1.perfil.show'))
            ->assertOk()
            ->assertJsonPath('id', $usuario->id)
            ->assertJsonPath('email', $usuario->email)
            ->assertJsonPath('irmao', null);
    }

    /**
     * Exemplo obrigatório: Irmão sem usuário vinculado continua válido no
     * cadastro — aqui, o inverso: usuário sem Irmão vinculado continua
     * válido no perfil da API (não quebra por falta de relação).
     */
    public function test_profile_includes_linked_irmao_when_present(): void
    {
        $irmao = Irmao::factory()->create(['nome_completo' => 'Fulano de Tal']);
        $usuario = User::factory()->create(['irmao_id' => $irmao->id]);

        $this->actingAs($usuario, 'sanctum')
            ->getJson(route('api.v1.perfil.show'))
            ->assertOk()
            ->assertJsonPath('irmao.nome_completo', 'Fulano de Tal')
            ->assertJsonMissingPath('irmao.cpf');
    }
}
