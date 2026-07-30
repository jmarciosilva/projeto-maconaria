<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\StatusFrequencia;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'evento_id',
    'irmao_id',
    'registrado_por_id',
    'status',
    'observacao',
])]
final class ChancelariaFrequencia extends Model
{
    protected $table = 'chancelaria_frequencias';

    protected function casts(): array
    {
        return [
            'status' => StatusFrequencia::class,
        ];
    }

    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class);
    }

    public function irmao(): BelongsTo
    {
        return $this->belongsTo(Irmao::class);
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por_id');
    }

    public function scopePresentes(Builder $query): Builder
    {
        return $query->where('status', StatusFrequencia::PRESENTE->value);
    }

    public function scopeAusencias(Builder $query): Builder
    {
        return $query->whereIn('status', [StatusFrequencia::AUSENTE->value, StatusFrequencia::JUSTIFICADO->value]);
    }
}
