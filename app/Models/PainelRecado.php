<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CategoriaRecadoPainel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'categoria',
    'titulo',
    'conteudo',
    'autor_id',
    'ativo',
    'publicado_em',
    'valido_ate',
])]
final class PainelRecado extends Model
{
    use SoftDeletes;

    protected $table = 'painel_recados';

    protected function casts(): array
    {
        return [
            'categoria' => CategoriaRecadoPainel::class,
            'ativo' => 'boolean',
            'publicado_em' => 'datetime',
            'valido_ate' => 'date',
        ];
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autor_id');
    }

    public function scopeVisiveis(Builder $query): Builder
    {
        return $query
            ->where('ativo', true)
            ->whereNotNull('publicado_em')
            ->where('publicado_em', '<=', now())
            ->where(function (Builder $query): void {
                $query->whereNull('valido_ate')->orWhereDate('valido_ate', '>=', now()->toDateString());
            });
    }

    public function scopeDaCategoria(Builder $query, CategoriaRecadoPainel $categoria): Builder
    {
        return $query->where('categoria', $categoria->value);
    }
}
