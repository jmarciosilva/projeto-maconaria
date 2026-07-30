<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\DocumentoAtividade;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin DocumentoAtividade
 */
final class DocumentoAtividadeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'descricao' => $this->descricao,
            'status' => $this->status->value,
            'status_rotulo' => $this->status->rotulo(),
            'publicado_em' => $this->publicado_em?->toIso8601String(),
            'prazo_entrega_em' => $this->prazo_entrega_em?->toIso8601String(),
            // "entregas" é carregada já filtrada pelo usuário autenticado
            // (ver DocumentoController::comEntregasDoUsuario) — nunca expõe
            // entregas de outros usuários por este endpoint.
            'minhas_entregas' => DocumentoEntregaResource::collection($this->whenLoaded('entregas')),
        ];
    }
}
