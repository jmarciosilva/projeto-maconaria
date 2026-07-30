<?php

namespace Tests\Unit;

use App\Enums\MailerEmail;
use App\Models\ConfiguracaoEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ConfiguracaoEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_atual_always_returns_the_same_singleton_record(): void
    {
        $primeira = ConfiguracaoEmail::atual();
        $primeira->update(['host' => 'smtp.exemplo.com']);

        $segunda = ConfiguracaoEmail::atual();
        $terceira = ConfiguracaoEmail::atual();

        $this->assertSame($primeira->id, $segunda->id);
        $this->assertSame($primeira->id, $terceira->id);
        $this->assertSame('smtp.exemplo.com', $segunda->host);
        $this->assertSame(1, ConfiguracaoEmail::count());
    }

    public function test_mailer_defaults_to_log_and_inactive(): void
    {
        $configuracao = ConfiguracaoEmail::atual();

        $this->assertSame(MailerEmail::LOG, $configuracao->mailer);
        $this->assertFalse($configuracao->ativo);
    }

    public function test_senha_is_encrypted_at_rest(): void
    {
        $configuracao = ConfiguracaoEmail::atual();
        $configuracao->update(['senha' => 'segredo-smtp']);

        $valorBruto = DB::table('configuracoes_email')->where('id', $configuracao->id)->value('senha');

        $this->assertNotSame('segredo-smtp', $valorBruto);
        $this->assertStringNotContainsString('segredo-smtp', (string) $valorBruto);
        $this->assertSame('segredo-smtp', $configuracao->fresh()->senha);
    }

    public function test_senha_is_hidden_from_array_and_json_serialization(): void
    {
        $configuracao = ConfiguracaoEmail::atual();
        $configuracao->update(['senha' => 'segredo-smtp']);

        $this->assertArrayNotHasKey('senha', $configuracao->fresh()->toArray());
        $this->assertStringNotContainsString('segredo-smtp', $configuracao->fresh()->toJson());
    }

    public function test_possui_senha_configurada_reflects_whether_a_password_was_set(): void
    {
        $configuracao = ConfiguracaoEmail::atual();

        $this->assertFalse($configuracao->possuiSenhaConfigurada());

        $configuracao->update(['senha' => 'segredo-smtp']);

        $this->assertTrue($configuracao->fresh()->possuiSenhaConfigurada());
    }
}
