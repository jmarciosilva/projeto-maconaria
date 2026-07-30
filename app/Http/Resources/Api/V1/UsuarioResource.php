<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
final class UsuarioResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'telefone' => $this->telefone,
            'perfis' => $this->getRoleNames(),
            'irmao' => $this->relationLoaded('irmao') && $this->irmao
                ? new IrmaoResumoResource($this->irmao)
                : null,
        ];
    }
}
