<?php

namespace Tests\Unit;

use App\Models\ConfiguracaoEmail;
use App\Support\Email\AplicadorConfiguracaoEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AplicadorConfiguracaoEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_does_not_override_mail_config_when_no_record_exists(): void
    {
        config(['mail.default' => 'log']);

        AplicadorConfiguracaoEmail::aplicar();

        $this->assertSame('log', config('mail.default'));
    }

    public function test_does_not_override_mail_config_when_inactive(): void
    {
        config(['mail.default' => 'log']);

        ConfiguracaoEmail::query()->create([
            'mailer' => 'smtp',
            'host' => 'smtp.exemplo.com',
            'ativo' => false,
        ]);

        AplicadorConfiguracaoEmail::aplicar();

        $this->assertSame('log', config('mail.default'));
    }

    public function test_overrides_mail_config_when_active(): void
    {
        ConfiguracaoEmail::query()->create([
            'mailer' => 'smtp',
            'host' => 'smtp.exemplo.com',
            'porta' => 587,
            'usuario' => 'contato@exemplo.com',
            'senha' => 'segredo-smtp',
            'criptografia' => 'tls',
            'remetente_nome' => 'ARLS Ferraz de Vasconcelos',
            'remetente_email' => 'contato@exemplo.com',
            'ativo' => true,
        ]);

        AplicadorConfiguracaoEmail::aplicar();

        $this->assertSame('smtp', config('mail.default'));
        $this->assertSame('smtp.exemplo.com', config('mail.mailers.smtp.host'));
        $this->assertSame(587, config('mail.mailers.smtp.port'));
        $this->assertSame('contato@exemplo.com', config('mail.mailers.smtp.username'));
        $this->assertSame('segredo-smtp', config('mail.mailers.smtp.password'));
        $this->assertSame('tls', config('mail.mailers.smtp.encryption'));
        $this->assertSame('contato@exemplo.com', config('mail.from.address'));
        $this->assertSame('ARLS Ferraz de Vasconcelos', config('mail.from.name'));
    }
}
