<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\DocumentoEntrega;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin DocumentoEntrega
 */
final class DocumentoEntregaResource extends JsonResource
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
            'enviado_em' => $this->enviado_em?->toIso8601String(),
            'arquivos' => DocumentoArquivoResource::collection($this->whenLoaded('arquivos')),
            'avaliacao' => $this->whenLoaded('avaliacao', fn () => $this->avaliacao ? [
                'nota' => $this->avaliacao->nota,
                'parecer' => $this->avaliacao->parecer,
                'avaliado_em' => $this->avaliacao->avaliado_em?->toIso8601String(),
            ] : null),
        ];
    }
}
