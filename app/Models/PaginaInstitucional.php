<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PaginaInstitucionalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'slug',
    'titulo',
    'conteudo',
    'meta_titulo',
    'meta_descricao',
    'publicado',
])]
final class PaginaInstitucional extends Model
{
    /** @use HasFactory<PaginaInstitucionalFactory> */
    use HasFactory;

    protected $table = 'paginas_institucionais';

    protected function casts(): array
    {
        return [
            'publicado' => 'boolean',
        ];
    }

    public function scopePublicado(Builder $query): Builder
    {
        return $query->where('publicado', true);
    }

    public function tituloDeMetadados(): string
    {
        return $this->meta_titulo ?: $this->titulo;
    }
}
