<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\NoticiaTagFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'nome',
    'slug',
])]
final class NoticiaTag extends Model
{
    /** @use HasFactory<NoticiaTagFactory> */
    use HasFactory;

    protected $table = 'noticia_tags';

    public function noticias(): BelongsToMany
    {
        return $this->belongsToMany(Noticia::class, 'noticia_tag');
    }
}
