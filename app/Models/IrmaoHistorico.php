<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TipoHistoricoIrmao;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'irmao_id',
    'tipo',
    'valor_anterior',
    'valor_novo',
    'data_referencia',
    'observacao',
    'registrado_por',
])]
final class IrmaoHistorico extends Model
{
    public $timestamps = false;

    const CREATED_AT = 'criado_em';

    protected function casts(): array
    {
        return [
            'tipo' => TipoHistoricoIrmao::class,
            'data_referencia' => 'date',
            'criado_em' => 'datetime',
        ];
    }

    public function irmao(): BelongsTo
    {
        return $this->belongsTo(Irmao::class);
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }
}
