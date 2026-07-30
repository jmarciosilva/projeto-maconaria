<?php

declare(strict_types=1);

namespace App\Support\Email;

use App\Models\ConfiguracaoEmail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

/**
 * Aplica em tempo de execução a configuração de e-mail cadastrada pelo
 * painel (tabela configuracoes_email) sobre o array "mail" do config(),
 * substituindo os valores fixos do .env. Chamado a cada requisição a partir
 * de AppServiceProvider::boot().
 */
final class AplicadorConfiguracaoEmail
{
    public static function aplicar(): void
    {
        // Guarda contra o boot do próprio `php artisan migrate` inicial (ou
        // testes que ainda não rodaram as migrations): sem essa checagem, a
        // aplicação inteira quebraria por falta da tabela antes dela existir.
        if (! Schema::hasTable('configuracoes_email')) {
            return;
        }

        $configuracao = ConfiguracaoEmail::query()->first();

        // Enquanto o administrador não configurar e ativar o SMTP, mantém o
        // mailer padrão do .env (log) — evita e-mails "presos" tentando sair
        // por um SMTP incompleto ou ainda não testado.
        if (! $configuracao || ! $configuracao->ativo) {
            return;
        }

        Config::set('mail.default', $configuracao->mailer->value);
        Config::set('mail.mailers.smtp.host', $configuracao->host);
        Config::set('mail.mailers.smtp.port', $configuracao->porta);
        Config::set('mail.mailers.smtp.username', $configuracao->usuario);
        Config::set('mail.mailers.smtp.password', $configuracao->senha);
        Config::set('mail.mailers.smtp.encryption', $configuracao->criptografia?->value);
        Config::set('mail.from.address', $configuracao->remetente_email);
        Config::set('mail.from.name', $configuracao->remetente_nome);
    }
}
