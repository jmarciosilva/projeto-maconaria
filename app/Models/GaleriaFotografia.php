<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['album_id', 'enviado_por_id', 'titulo', 'texto_alternativo', 'caminho', 'mime', 'tamanho', 'ordem'])]
final class GaleriaFotografia extends Model
{
    protected $table = 'galeria_fotografias';

    protected function casts(): array
    {
        return [
            'tamanho' => 'integer',
            'ordem' => 'integer',
        ];
    }

    public function album(): BelongsTo
    {
        return $this->belongsTo(GaleriaAlbum::class, 'album_id');
    }

    public function enviadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enviado_por_id');
    }
}
