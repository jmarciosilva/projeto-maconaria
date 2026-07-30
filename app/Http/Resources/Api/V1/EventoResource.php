<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\Evento;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Evento
 */
final class EventoResource extends JsonResource
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
            'descricao' => $this->descricao,
            'tipo' => $this->tipo->value,
            'visibilidade' => $this->visibilidade->value,
            'local' => $this->local,
            'inicio_em' => $this->inicio_em?->toIso8601String(),
            'fim_em' => $this->fim_em?->toIso8601String(),
            'inscricoes_ate' => $this->inscricoes_ate?->toIso8601String(),
            'capacidade' => $this->capacidade,
            'permite_confirmacao' => $this->permite_confirmacao,
            'aceita_confirmacao' => $this->aceitaConfirmacao(),
            'possui_vaga_disponivel' => $this->possuiVagaDisponivel(),
            // Presente apenas quando o usuário autenticado tem uma confirmação
            // própria carregada junto (ver EventoController::comConfirmacaoDoUsuario).
            'minha_confirmacao' => $this->whenLoaded(
                'confirmacoes',
                fn () => $this->confirmacoes->first()?->status->value,
            ),
        ];
    }
}
