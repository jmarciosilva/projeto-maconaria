<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_and_receive_a_token(): void
    {
        $usuario = User::factory()->create(['password' => bcrypt('senha-correta')]);

        $resposta = $this->postJson(route('api.v1.auth.login'), [
            'email' => $usuario->email,
            'password' => 'senha-correta',
            'device_name' => 'iphone-de-teste',
        ]);

        $resposta->assertOk()->assertJsonStructure(['token', 'usuario' => ['id', 'name', 'email']]);

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $usuario->id,
            'name' => 'iphone-de-teste',
        ]);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $usuario = User::factory()->create(['password' => bcrypt('senha-correta')]);

        $this->postJson(route('api.v1.auth.login'), [
            'email' => $usuario->email,
            'password' => 'senha-errada',
            'device_name' => 'iphone-de-teste',
        ])->assertUnprocessable()->assertJsonValidationErrors('email');

        $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $usuario->id]);
    }

    /**
     * Exemplo obrigatório: usuário inativo não consegue autenticar (versão API).
     */
    public function test_inactive_user_cannot_login(): void
    {
        $usuario = User::factory()->create(['password' => bcrypt('senha-correta'), 'bloqueado_em' => now()]);

        $this->postJson(route('api.v1.auth.login'), [
            'email' => $usuario->email,
            'password' => 'senha-correta',
            'device_name' => 'iphone-de-teste',
        ])->assertUnprocessable()->assertJsonValidationErrors('email');

        $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $usuario->id]);
    }

    public function test_protected_route_requires_a_token(): void
    {
        $this->getJson(route('api.v1.perfil.show'))->assertUnauthorized();
    }

    public function test_logout_revokes_the_current_token(): void
    {
        $usuario = User::factory()->create();
        $token = $usuario->createToken('teste')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson(route('api.v1.auth.logout'))
            ->assertOk();

        $this->assertSame(0, PersonalAccessToken::query()->where('tokenable_id', $usuario->id)->count());
    }
}
