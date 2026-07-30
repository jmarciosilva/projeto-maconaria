<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['atividade_id', 'entrega_id', 'usuario_id', 'comentario'])]
final class DocumentoComentario extends Model
{
    protected $table = 'documento_comentarios';

    public function atividade(): BelongsTo
    {
        return $this->belongsTo(DocumentoAtividade::class, 'atividade_id');
    }

    public function entrega(): BelongsTo
    {
        return $this->belongsTo(DocumentoEntrega::class, 'entrega_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
