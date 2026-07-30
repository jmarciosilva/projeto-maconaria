<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\NoticiaCategoriaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'nome',
    'slug',
    'descricao',
    'ativa',
])]
final class NoticiaCategoria extends Model
{
    /** @use HasFactory<NoticiaCategoriaFactory> */
    use HasFactory;

    protected $table = 'noticia_categorias';

    protected function casts(): array
    {
        return [
            'ativa' => 'boolean',
        ];
    }

    public function noticias(): HasMany
    {
        return $this->hasMany(Noticia::class, 'categoria_id');
    }
}
