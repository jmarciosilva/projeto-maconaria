<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\StatusEvento;
use App\Enums\TipoEvento;
use App\Enums\VisibilidadeEvento;
use Database\Factories\EventoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'autor_id',
    'titulo',
    'slug',
    'descricao',
    'tipo',
    'status',
    'visibilidade',
    'local',
    'inicio_em',
    'fim_em',
    'inscricoes_ate',
    'capacidade',
    'permite_confirmacao',
])]
final class Evento extends Model
{
    /** @use HasFactory<EventoFactory> */
    use HasFactory;

    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'tipo' => TipoEvento::class,
            'status' => StatusEvento::class,
            'visibilidade' => VisibilidadeEvento::class,
            'inicio_em' => 'datetime',
            'fim_em' => 'datetime',
            'inscricoes_ate' => 'datetime',
            'capacidade' => 'integer',
            'permite_confirmacao' => 'boolean',
        ];
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autor_id');
    }

    public function confirmacoes(): HasMany
    {
        return $this->hasMany(EventoConfirmacaoPresenca::class);
    }

    public function scopePublicado(Builder $query): Builder
    {
        return $query->where('status', StatusEvento::PUBLICADO->value);
    }

    public function scopePublicoNoSite(Builder $query): Builder
    {
        return $query
            ->publicado()
            ->where('visibilidade', VisibilidadeEvento::PUBLICA->value);
    }

    public function scopeVisivelNaAreaRestrita(Builder $query): Builder
    {
        return $query->publicado();
    }

    public function scopeFuturo(Builder $query): Builder
    {
        return $query->where('inicio_em', '>=', now()->startOfDay());
    }

    public function confirmacoesAtivas(): HasMany
    {
        return $this->confirmacoes()->confirmadas();
    }

    public function possuiVagaDisponivel(): bool
    {
        if ($this->capacidade === null) {
            return true;
        }

        return $this->confirmacoesAtivas()->count() < $this->capacidade;
    }

    public function aceitaConfirmacao(): bool
    {
        return $this->permite_confirmacao
            && $this->status === StatusEvento::PUBLICADO
            && $this->inicio_em->isFuture()
            && ($this->inscricoes_ate === null || $this->inscricoes_ate->isFuture());
    }
}
