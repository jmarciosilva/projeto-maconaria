<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['caminho', 'descricao', 'ordem'])]
final class NoticiaFoto extends Model
{
    public function noticia(): BelongsTo
    {
        return $this->belongsTo(Noticia::class);
    }
}
