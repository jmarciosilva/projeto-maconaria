<?php

namespace Tests\Feature\Admin;

use App\Models\Auditoria;
use App\Models\User;
use Database\Seeders\PerfilPermissaoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsuarioControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_permission_cannot_view_users_list(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();

        $this->actingAs($usuario)
            ->get(route('admin.usuarios.index'))
            ->assertForbidden();
    }

    public function test_user_with_permission_can_view_users_list(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('usuarios.visualizar');

        $this->actingAs($usuario)
            ->get(route('admin.usuarios.index'))
            ->assertOk();
    }

    public function test_superadministrador_bypasses_permission_checks(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $superadmin = User::factory()->create();
        $superadmin->assignRole('Superadministrador');

        $this->actingAs($superadmin)
            ->get(route('admin.usuarios.index'))
            ->assertOk();
    }

    public function test_updating_a_user_creates_an_audit_record(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $administrador = User::factory()->create();
        $administrador->givePermissionTo('usuarios.editar');

        $alvo = User::factory()->create(['name' => 'Nome Antigo']);

        $this->actingAs($administrador)
            ->put(route('admin.usuarios.update', $alvo), [
                'name' => 'Nome Novo',
                'email' => $alvo->email,
            ])
            ->assertRedirect(route('admin.usuarios.index'));

        $this->assertDatabaseHas('auditorias', [
            'acao' => 'editar',
            'modulo' => 'usuarios',
            'entidade_id' => $alvo->id,
            'usuario_id' => $administrador->id,
        ]);

        $registro = Auditoria::where('entidade_id', $alvo->id)->firstOrFail();
        $this->assertSame('Nome Novo', $registro->dados_novos['name']);
    }

    public function test_telefone_with_invalid_format_is_rejected(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $administrador = User::factory()->create();
        $administrador->givePermissionTo('usuarios.criar');

        $this->actingAs($administrador)
            ->post(route('admin.usuarios.store'), [
                'name' => 'Novo Usuário',
                'email' => 'novo.usuario@example.com',
                'password' => 'senha-forte-123',
                'telefone' => '11987654321',
            ])
            ->assertSessionHasErrors('telefone');

        $this->assertDatabaseMissing('users', ['email' => 'novo.usuario@example.com']);
    }

    public function test_telefone_with_valid_format_is_accepted(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $administrador = User::factory()->create();
        $administrador->givePermissionTo('usuarios.criar');

        $this->actingAs($administrador)
            ->post(route('admin.usuarios.store'), [
                'name' => 'Novo Usuário',
                'email' => 'novo.usuario@example.com',
                'password' => 'senha-forte-123',
                'telefone' => '(11) 98765-4321',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'email' => 'novo.usuario@example.com',
            'telefone' => '(11) 98765-4321',
        ]);
    }
}
