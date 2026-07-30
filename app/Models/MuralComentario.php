<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['publicacao_id', 'usuario_id', 'comentario', 'aprovado', 'aprovado_em'])]
final class MuralComentario extends Model
{
    use SoftDeletes;

    protected $table = 'mural_comentarios';

    protected function casts(): array
    {
        return [
            'aprovado' => 'boolean',
            'aprovado_em' => 'datetime',
        ];
    }

    public function publicacao(): BelongsTo
    {
        return $this->belongsTo(MuralPublicacao::class, 'publicacao_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
