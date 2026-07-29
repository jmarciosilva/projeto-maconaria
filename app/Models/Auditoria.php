<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'usuario_id',
    'acao',
    'modulo',
    'entidade',
    'entidade_id',
    'dados_anteriores',
    'dados_novos',
    'endereco_ip',
    'user_agent',
])]
final class Auditoria extends Model
{
    public $timestamps = false;

    const CREATED_AT = 'criado_em';

    protected function casts(): array
    {
        return [
            'dados_anteriores' => 'array',
            'dados_novos' => 'array',
            'criado_em' => 'datetime',
        ];
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
