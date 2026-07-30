<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\MuralPublicacao;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin MuralPublicacao
 */
final class MuralPublicacaoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'conteudo' => $this->conteudo,
            'publicado_em' => $this->publicado_em?->toIso8601String(),
            'autor' => $this->whenLoaded('autor', fn () => $this->autor->name),
            'total_comentarios' => $this->comentarios_aprovados_count ?? null,
            'total_reacoes' => $this->reacoes_count ?? null,
            // "reacoes" só vem carregada (e já filtrada pelo usuário atual)
            // quando autenticado — ver EventoController::consulta() para o
            // mesmo padrão de eager load restrito ao próprio usuário.
            'reagido_pelo_usuario_atual' => $this->relationLoaded('reacoes')
                ? $this->reacoes->isNotEmpty()
                : null,
        ];
    }
}
