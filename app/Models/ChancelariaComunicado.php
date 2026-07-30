<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\StatusComunicadoChancelaria;
use Database\Factories\ChancelariaComunicadoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'autor_id',
    'titulo',
    'conteudo',
    'status',
    'publicado_em',
])]
final class ChancelariaComunicado extends Model
{
    /** @use HasFactory<ChancelariaComunicadoFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $table = 'chancelaria_comunicados';

    protected function casts(): array
    {
        return [
            'status' => StatusComunicadoChancelaria::class,
            'publicado_em' => 'datetime',
        ];
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autor_id');
    }
}
