<?php

namespace Tests\Feature\Auth;

use App\Enums\StatusUsuario;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_user_cannot_authenticate(): void
    {
        $user = User::factory()->create(['status' => StatusUsuario::INATIVO]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_blocked_user_cannot_authenticate(): void
    {
        $user = User::factory()->create(['bloqueado_em' => now()]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('area-restrita', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    /**
     * A exigência de e-mail verificado está temporariamente desativada (sem
     * SMTP configurado ainda não há como o usuário receber o link de
     * verificação — ver docs/MODULOS.md). Este teste documenta esse
     * comportamento deliberado; deve ser revisado quando o middleware
     * "verified" for reativado nas rotas de área restrita/admin.
     */
    public function test_unverified_user_can_access_area_restrita_while_verification_is_disabled(): void
    {
        $usuario = User::factory()->unverified()->create();

        $this->actingAs($usuario)
            ->get(route('area-restrita'))
            ->assertOk();
    }
}
