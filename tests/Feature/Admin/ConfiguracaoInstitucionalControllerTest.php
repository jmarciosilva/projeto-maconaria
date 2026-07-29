<?php

namespace Tests\Feature\Admin;

use App\Models\ConfiguracaoInstitucional;
use App\Models\User;
use Database\Seeders\PerfilPermissaoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ConfiguracaoInstitucionalControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_permission_cannot_view_configuracoes(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();

        $this->actingAs($usuario)
            ->get(route('admin.configuracoes.institucional.edit'))
            ->assertForbidden();
    }

    public function test_user_with_permission_can_view_configuracoes(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('cms.visualizar');

        $this->actingAs($usuario)
            ->get(route('admin.configuracoes.institucional.edit'))
            ->assertOk();
    }

    public function test_user_without_permission_cannot_update_configuracoes(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('cms.visualizar');

        $this->actingAs($usuario)
            ->put(route('admin.configuracoes.institucional.update'), [
                'email_institucional' => 'contato@example.com',
            ])
            ->assertForbidden();
    }

    public function test_user_with_permission_can_update_configuracoes_and_upload_logo(): void
    {
        Storage::fake('public');

        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('cms.editar');

        $this->actingAs($usuario)->put(route('admin.configuracoes.institucional.update'), [
            'endereco_rodape' => 'Rua Exemplo, 123',
            'email_institucional' => 'contato@example.com',
            'facebook_url' => 'https://facebook.com/exemplo',
            'instagram_url' => 'https://instagram.com/exemplo',
            'twitter_url' => 'https://x.com/exemplo',
            'tiktok_url' => 'https://tiktok.com/@exemplo',
            'logotipo' => UploadedFile::fake()->image('logo.png'),
        ])->assertRedirect(route('admin.configuracoes.institucional.edit'));

        $configuracao = ConfiguracaoInstitucional::atual();

        $this->assertSame('Rua Exemplo, 123', $configuracao->endereco_rodape);
        $this->assertSame('contato@example.com', $configuracao->email_institucional);
        $this->assertSame('https://facebook.com/exemplo', $configuracao->facebook_url);
        $this->assertSame('https://tiktok.com/@exemplo', $configuracao->tiktok_url);
        $this->assertNotNull($configuracao->logotipo);
        Storage::disk('public')->assertExists($configuracao->logotipo);
    }

    public function test_invalid_social_media_url_is_rejected(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('cms.editar');

        $this->actingAs($usuario)
            ->put(route('admin.configuracoes.institucional.update'), [
                'facebook_url' => 'nao-e-uma-url',
            ])
            ->assertSessionHasErrors('facebook_url');
    }

    public function test_user_with_permission_can_update_identidade_institucional(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('cms.editar');

        $this->actingAs($usuario)->put(route('admin.configuracoes.institucional.update'), [
            'nome_loja' => 'ARLS Ferraz de Vasconcelos',
            'titulo_institucional' => 'Bem-vindos à nossa Loja',
            'subtitulo_institucional' => 'Fraternidade, verdade e progresso.',
            'telefone_institucional' => '(11) 4444-5555',
        ])->assertRedirect(route('admin.configuracoes.institucional.edit'));

        $configuracao = ConfiguracaoInstitucional::atual();

        $this->assertSame('ARLS Ferraz de Vasconcelos', $configuracao->nome_loja);
        $this->assertSame('Bem-vindos à nossa Loja', $configuracao->titulo_institucional);
        $this->assertSame('Fraternidade, verdade e progresso.', $configuracao->subtitulo_institucional);
        $this->assertSame('(11) 4444-5555', $configuracao->telefone_institucional);
    }

    public function test_invalid_telefone_institucional_format_is_rejected(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('cms.editar');

        $this->actingAs($usuario)
            ->put(route('admin.configuracoes.institucional.update'), [
                'telefone_institucional' => '1144445555',
            ])
            ->assertSessionHasErrors('telefone_institucional');
    }

    public function test_nome_falls_back_to_app_name_when_not_configured(): void
    {
        $configuracao = ConfiguracaoInstitucional::atual();

        $this->assertSame(config('app.name'), $configuracao->nome());
    }
}
