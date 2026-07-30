<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['entrega_id', 'avaliador_id', 'nota', 'parecer', 'avaliado_em'])]
final class DocumentoAvaliacao extends Model
{
    protected $table = 'documento_avaliacoes';

    protected function casts(): array
    {
        return [
            'nota' => 'integer',
            'avaliado_em' => 'datetime',
        ];
    }

    public function entrega(): BelongsTo
    {
        return $this->belongsTo(DocumentoEntrega::class, 'entrega_id');
    }

    public function avaliador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'avaliador_id');
    }
}
