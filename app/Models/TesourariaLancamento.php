<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\StatusLancamentoFinanceiro;
use App\Enums\TipoLancamentoFinanceiro;
use Database\Factories\TesourariaLancamentoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'categoria_id',
    'conta_id',
    'irmao_id',
    'criado_por_id',
    'aprovado_por_id',
    'tipo',
    'status',
    'descricao',
    'valor_centavos',
    'data_competencia',
    'data_vencimento',
    'data_pagamento',
    'observacao',
    'aprovado_em',
])]
final class TesourariaLancamento extends Model
{
    /** @use HasFactory<TesourariaLancamentoFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $table = 'tesouraria_lancamentos';

    protected function casts(): array
    {
        return [
            'tipo' => TipoLancamentoFinanceiro::class,
            'status' => StatusLancamentoFinanceiro::class,
            'valor_centavos' => 'integer',
            'data_competencia' => 'date',
            'data_vencimento' => 'date',
            'data_pagamento' => 'date',
            'aprovado_em' => 'datetime',
        ];
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(TesourariaCategoria::class, 'categoria_id');
    }

    public function conta(): BelongsTo
    {
        return $this->belongsTo(TesourariaConta::class, 'conta_id');
    }

    public function irmao(): BelongsTo
    {
        return $this->belongsTo(Irmao::class, 'irmao_id');
    }
}
