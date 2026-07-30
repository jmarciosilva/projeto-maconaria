<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\StatusMuralGaleria;
use App\Enums\VisibilidadeMuralGaleria;
use Database\Factories\GaleriaAlbumFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['autor_id', 'titulo', 'slug', 'descricao', 'status', 'visibilidade', 'publicado_em'])]
final class GaleriaAlbum extends Model
{
    /** @use HasFactory<GaleriaAlbumFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $table = 'galeria_albuns';

    protected function casts(): array
    {
        return [
            'status' => StatusMuralGaleria::class,
            'visibilidade' => VisibilidadeMuralGaleria::class,
            'publicado_em' => 'datetime',
        ];
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autor_id');
    }

    public function fotografias(): HasMany
    {
        return $this->hasMany(GaleriaFotografia::class, 'album_id')->orderBy('ordem');
    }

    public function scopePublico(Builder $query): Builder
    {
        return $query
            ->where('status', StatusMuralGaleria::PUBLICADO->value)
            ->where('visibilidade', VisibilidadeMuralGaleria::PUBLICA->value);
    }
}
