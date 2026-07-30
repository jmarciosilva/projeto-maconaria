<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\Noticia;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Noticia
 */
final class NoticiaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'slug' => $this->slug,
            'resumo' => $this->resumo,
            'conteudo' => $this->conteudo,
            'destaque' => $this->destaque,
            'publicado_em' => $this->publicado_em?->toIso8601String(),
            'categoria' => $this->whenLoaded('categoria', fn () => [
                'id' => $this->categoria->id,
                'nome' => $this->categoria->nome,
                'slug' => $this->categoria->slug,
            ]),
            'tags' => $this->whenLoaded('tags', fn () => $this->tags->map(fn ($tag) => [
                'id' => $tag->id,
                'nome' => $tag->nome,
                'slug' => $tag->slug,
            ])),
        ];
    }
}
