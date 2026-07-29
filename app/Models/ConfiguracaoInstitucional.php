<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Configuração institucional é um registro único (singleton), acessado via
 * ConfiguracaoInstitucional::atual(). Evita a complexidade de uma tabela
 * chave-valor genérica para um punhado de campos que sempre existem juntos.
 *
 * Importante: não usar firstOrCreate(['id' => 1]) aqui — como "id" não é
 * mass-assignable, o Eloquent ignora silenciosamente esse valor ao criar o
 * registro, então o novo registro nunca fica com id = 1 de fato, e a
 * próxima chamada a atual() cria outro registro em branco (bug já
 * corrigido — ver docs/MODULOS.md).
 */
#[Fillable([
    'nome_loja',
    'titulo_institucional',
    'subtitulo_institucional',
    'telefone_institucional',
    'logotipo',
    'endereco_rodape',
    'email_institucional',
    'facebook_url',
    'instagram_url',
    'twitter_url',
    'tiktok_url',
])]
final class ConfiguracaoInstitucional extends Model
{
    protected $table = 'configuracoes_institucionais';

    public static function atual(): self
    {
        return self::query()->first() ?? self::query()->create();
    }

    public function possuiRedesSociais(): bool
    {
        return $this->facebook_url || $this->instagram_url || $this->twitter_url || $this->tiktok_url;
    }

    public function nome(): string
    {
        return $this->nome_loja ?: (string) config('app.name');
    }
}
