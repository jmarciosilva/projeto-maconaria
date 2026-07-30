<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ChancelariaVisitanteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'evento_id',
    'registrado_por_id',
    'nome',
    'loja_origem',
    'potencia',
    'documento',
    'observacao',
])]
final class ChancelariaVisitante extends Model
{
    /** @use HasFactory<ChancelariaVisitanteFactory> */
    use HasFactory;

    protected $table = 'chancelaria_visitantes';

    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class);
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por_id');
    }
}
