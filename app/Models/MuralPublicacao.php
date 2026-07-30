<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\StatusMuralGaleria;
use App\Enums\VisibilidadeMuralGaleria;
use Database\Factories\MuralPublicacaoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['autor_id', 'titulo', 'conteudo', 'status', 'visibilidade', 'publicado_em'])]
final class MuralPublicacao extends Model
{
    /** @use HasFactory<MuralPublicacaoFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $table = 'mural_publicacoes';

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

    public function comentarios(): HasMany
    {
        return $this->hasMany(MuralComentario::class, 'publicacao_id');
    }

    public function reacoes(): HasMany
    {
        return $this->hasMany(MuralReacao::class, 'publicacao_id');
    }

    public function scopePublico(Builder $query): Builder
    {
        return $query
            ->where('status', StatusMuralGaleria::PUBLICADO->value)
            ->where('visibilidade', VisibilidadeMuralGaleria::PUBLICA->value);
    }
}
