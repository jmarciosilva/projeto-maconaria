<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\StatusConfirmacaoPresenca;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'evento_id',
    'usuario_id',
    'status',
    'observacao',
])]
final class EventoConfirmacaoPresenca extends Model
{
    protected $table = 'evento_confirmacoes_presenca';

    protected function casts(): array
    {
        return [
            'status' => StatusConfirmacaoPresenca::class,
        ];
    }

    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function scopeConfirmadas(Builder $query): Builder
    {
        return $query->where('status', StatusConfirmacaoPresenca::CONFIRMADO->value);
    }
}
