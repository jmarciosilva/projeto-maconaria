<?php

namespace Tests\Feature\Admin;

use App\Mail\EmailTesteConfiguracao;
use App\Models\Auditoria;
use App\Models\ConfiguracaoEmail;
use App\Models\User;
use Database\Seeders\PerfilPermissaoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ConfiguracaoEmailControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_permission_cannot_view_configuracoes_email(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();

        $this->actingAs($usuario)
            ->get(route('admin.configuracoes.email.edit'))
            ->assertForbidden();
    }

    public function test_user_with_permission_can_view_configuracoes_email(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('configuracoes.visualizar');

        $this->actingAs($usuario)
            ->get(route('admin.configuracoes.email.edit'))
            ->assertOk();
    }

    /**
     * Exemplo obrigatório: configuração SMTP não deve revelar a senha.
     */
    public function test_smtp_password_is_never_rendered_in_the_edit_view(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        ConfiguracaoEmail::atual()->update(['senha' => 'segredo-super-secreto']);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('configuracoes.visualizar');

        $this->actingAs($usuario)
            ->get(route('admin.configuracoes.email.edit'))
            ->assertOk()
            ->assertDontSee('segredo-super-secreto');
    }

    public function test_user_without_permission_cannot_update_configuracoes_email(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('configuracoes.visualizar');

        $this->actingAs($usuario)
            ->put(route('admin.configuracoes.email.update'), [
                'mailer' => 'log',
                'remetente_nome' => 'Loja',
                'remetente_email' => 'contato@example.com',
            ])
            ->assertForbidden();
    }

    public function test_user_with_permission_can_update_smtp_configuration(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('configuracoes.editar');

        $this->actingAs($usuario)->put(route('admin.configuracoes.email.update'), [
            'mailer' => 'smtp',
            'host' => 'smtp.exemplo.com',
            'porta' => 587,
            'usuario' => 'contato@exemplo.com',
            'senha' => 'segredo-smtp',
            'criptografia' => 'tls',
            'remetente_nome' => 'ARLS Ferraz de Vasconcelos',
            'remetente_email' => 'contato@exemplo.com',
            'ativo' => '1',
        ])->assertRedirect(route('admin.configuracoes.email.edit'));

        $configuracao = ConfiguracaoEmail::atual();

        $this->assertSame('smtp.exemplo.com', $configuracao->host);
        $this->assertSame(587, $configuracao->porta);
        $this->assertTrue($configuracao->ativo);
        $this->assertSame('segredo-smtp', $configuracao->senha);
    }

    public function test_blank_password_keeps_the_previously_stored_password(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        ConfiguracaoEmail::atual()->update(['senha' => 'senha-original']);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('configuracoes.editar');

        $this->actingAs($usuario)->put(route('admin.configuracoes.email.update'), [
            'mailer' => 'log',
            'remetente_nome' => 'ARLS Ferraz de Vasconcelos',
            'remetente_email' => 'contato@exemplo.com',
        ]);

        $this->assertSame('senha-original', ConfiguracaoEmail::atual()->senha);
    }

    /**
     * Exemplo obrigatório: alteração de configuração deve gerar auditoria,
     * sem nunca registrar a senha (nem antes, nem depois).
     */
    public function test_updating_configuracoes_email_generates_audit_without_the_password(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('configuracoes.editar');

        $this->actingAs($usuario)->put(route('admin.configuracoes.email.update'), [
            'mailer' => 'smtp',
            'host' => 'smtp.exemplo.com',
            'porta' => 587,
            'senha' => 'segredo-que-nao-pode-vazar',
            'remetente_nome' => 'ARLS Ferraz de Vasconcelos',
            'remetente_email' => 'contato@exemplo.com',
            'ativo' => '1',
        ]);

        $registro = Auditoria::where('entidade', 'ConfiguracaoEmail')->where('acao', 'editar')->firstOrFail();

        $this->assertArrayNotHasKey('senha', $registro->dados_novos ?? []);
        $this->assertArrayNotHasKey('senha', $registro->dados_anteriores ?? []);
        $this->assertStringNotContainsString('segredo-que-nao-pode-vazar', (string) json_encode($registro->dados_novos));
    }

    public function test_invalid_criptografia_is_rejected(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('configuracoes.editar');

        $this->actingAs($usuario)
            ->put(route('admin.configuracoes.email.update'), [
                'mailer' => 'smtp',
                'host' => 'smtp.exemplo.com',
                'porta' => 587,
                'remetente_nome' => 'Loja',
                'remetente_email' => 'contato@example.com',
                'criptografia' => 'md5',
            ])
            ->assertSessionHasErrors('criptografia');
    }

    public function test_host_and_porta_are_required_when_mailer_is_smtp(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('configuracoes.editar');

        $this->actingAs($usuario)
            ->put(route('admin.configuracoes.email.update'), [
                'mailer' => 'smtp',
                'remetente_nome' => 'Loja',
                'remetente_email' => 'contato@example.com',
            ])
            ->assertSessionHasErrors(['host', 'porta']);
    }

    public function test_user_without_permission_cannot_send_test_email(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();

        $this->actingAs($usuario)
            ->post(route('admin.configuracoes.email.teste'), ['destinatario' => 'destino@example.com'])
            ->assertForbidden();
    }

    public function test_test_email_is_sent_successfully_and_audited(): void
    {
        Mail::fake();

        $this->seed(PerfilPermissaoSeeder::class);

        ConfiguracaoEmail::atual()->update(['mailer' => 'log']);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('configuracoes.editar');

        $this->actingAs($usuario)
            ->post(route('admin.configuracoes.email.teste'), ['destinatario' => 'destino@example.com'])
            ->assertRedirect()
            ->assertSessionHas('sucesso');

        Mail::assertSent(EmailTesteConfiguracao::class, fn ($mail) => $mail->hasTo('destino@example.com'));

        $this->assertDatabaseHas('auditorias', [
            'entidade' => 'ConfiguracaoEmail',
            'acao' => 'testar-envio-email',
        ]);
    }

    /**
     * Exemplo obrigatório: falha no envio deve ser tratada com mensagem
     * amigável, sem vazar credenciais nem stack trace técnico.
     */
    public function test_test_email_failure_shows_friendly_message_and_does_not_leak_credentials(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        ConfiguracaoEmail::atual()->update([
            'mailer' => 'smtp',
            'host' => '127.0.0.1',
            'porta' => 1,
            'senha' => 'segredo-que-nao-pode-vazar',
            'remetente_nome' => 'Loja',
            'remetente_email' => 'contato@exemplo.com',
            'ativo' => true,
        ]);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('configuracoes.editar');

        $this->actingAs($usuario)
            ->post(route('admin.configuracoes.email.teste'), ['destinatario' => 'destino@example.com'])
            ->assertRedirect();

        $erro = session('erro');

        $this->assertNotNull($erro);
        $this->assertStringNotContainsString('segredo-que-nao-pode-vazar', $erro);

        $this->assertDatabaseHas('auditorias', [
            'entidade' => 'ConfiguracaoEmail',
            'acao' => 'testar-envio-email-falhou',
        ]);
    }
}
