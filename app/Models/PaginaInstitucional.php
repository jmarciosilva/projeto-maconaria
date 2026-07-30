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

    /**
     * As seis páginas com slug fixo (ver routes/web.php) têm uma URL "bonita"
     * própria; qualquer outra página cadastrada livremente pelo painel cai
     * na rota genérica /institucional/{slug}. Centralizado aqui para não
     * duplicar esse mapeamento entre o layout público e a página inicial.
     */
    public function urlPublica(): string
    {
        return match ($this->slug) {
            'sobre-nos' => route('paginas.sobre-nos'),
            'nossa-historia' => route('paginas.nossa-historia'),
            'o-que-e-maconaria' => route('paginas.maconaria'),
            'maconaria-para-jovens' => route('paginas.maconaria-jovens'),
            'mudando-o-cidadao' => route('paginas.mudar-cidadao'),
            'politica-privacidade' => route('paginas.politica-privacidade'),
            'termos-de-uso' => route('paginas.termos-de-uso'),
            default => route('paginas.mostrar', $this->slug),
        };
    }
}
