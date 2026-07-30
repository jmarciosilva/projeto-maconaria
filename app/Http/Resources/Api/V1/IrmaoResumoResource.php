<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\Irmao;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Irmao
 */
final class IrmaoResumoResource extends JsonResource
{
    /**
     * Campos sensíveis do Irmão (CPF, RG, observações administrativas) nunca
     * são incluídos aqui — mesma restrição já aplicada no painel web.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome_completo' => $this->nome_completo,
            'nome_social' => $this->nome_social,
            'grau_atual' => $this->grau_atual?->value,
            'grau_atual_rotulo' => $this->grau_atual?->rotulo(),
            'situacao_cadastral' => $this->situacao_cadastral?->value,
            'cargo_atual' => $this->cargo_atual,
            'data_ingresso_loja' => $this->data_ingresso_loja?->toDateString(),
        ];
    }
}
