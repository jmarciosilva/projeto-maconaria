<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\StatusNoticia;
use App\Enums\VisibilidadeNoticia;
use Database\Factories\NoticiaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'categoria_id',
    'autor_id',
    'titulo',
    'slug',
    'resumo',
    'conteudo',
    'status',
    'visibilidade',
    'destaque',
    'publicado_em',
    'agendado_para',
])]
final class Noticia extends Model
{
    /** @use HasFactory<NoticiaFactory> */
    use HasFactory;

    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => StatusNoticia::class,
            'visibilidade' => VisibilidadeNoticia::class,
            'destaque' => 'boolean',
            'publicado_em' => 'datetime',
            'agendado_para' => 'datetime',
        ];
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(NoticiaCategoria::class, 'categoria_id');
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autor_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(NoticiaTag::class, 'noticia_tag');
    }

    public function versoes(): HasMany
    {
        return $this->hasMany(NoticiaVersao::class);
    }

    public function scopePublicaNoSite(Builder $query): Builder
    {
        return $query
            ->where('status', StatusNoticia::PUBLICADA->value)
            ->where('visibilidade', VisibilidadeNoticia::PUBLICA->value)
            ->where(function (Builder $query) {
                $query->whereNull('publicado_em')
                    ->orWhere('publicado_em', '<=', now());
            });
    }

    public function scopeDestaque(Builder $query): Builder
    {
        return $query->where('destaque', true);
    }

    public function estaPublicaNoSite(): bool
    {
        return $this->status === StatusNoticia::PUBLICADA
            && $this->visibilidade === VisibilidadeNoticia::PUBLICA
            && ($this->publicado_em === null || $this->publicado_em->lessThanOrEqualTo(now()));
    }
}
