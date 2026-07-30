<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CriptografiaEmail;
use App\Enums\MailerEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Configuração de e-mail (SMTP) é um registro único (singleton), acessado
 * via ConfiguracaoEmail::atual() — mesmo padrão de ConfiguracaoInstitucional.
 */
#[Fillable([
    'mailer',
    'host',
    'porta',
    'usuario',
    'senha',
    'criptografia',
    'remetente_nome',
    'remetente_email',
    'ativo',
])]
final class ConfiguracaoEmail extends Model
{
    protected $table = 'configuracoes_email';

    /**
     * A senha nunca é exposta em serializações (array/JSON) do Model, mesmo
     * que um controller ou uma view a inclua por engano em compact()/toArray().
     *
     * @var list<string>
     */
    protected $hidden = ['senha'];

    protected function casts(): array
    {
        return [
            'mailer' => MailerEmail::class,
            'porta' => 'integer',
            // O cast "encrypted" nativo do Eloquent cuida da criptografia em
            // repouso usando APP_KEY (Crypt) — sem reinventar criptografia
            // própria para um dado sensível.
            'senha' => 'encrypted',
            'criptografia' => CriptografiaEmail::class,
            'ativo' => 'boolean',
        ];
    }

    public static function atual(): self
    {
        // Os valores default são passados explicitamente aqui (e não apenas
        // no default() da coluna na migration): create() com array vazio não
        // preenche os atributos em memória da instância recém-criada com os
        // defaults do banco — só a próxima leitura (fresh()/refresh()) os
        // traria. Sem isso, o primeiro acesso a ->mailer logo após a criação
        // do singleton retornaria null em vez de MailerEmail::LOG.
        return self::query()->first() ?? self::query()->create([
            'mailer' => MailerEmail::LOG,
            'ativo' => false,
        ]);
    }

    public function possuiSenhaConfigurada(): bool
    {
        return filled($this->senha);
    }
}
