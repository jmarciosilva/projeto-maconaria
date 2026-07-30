<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\DocumentoArquivo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin DocumentoArquivo
 */
final class DocumentoArquivoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome_original' => $this->nome_original,
            'mime' => $this->mime,
            'tamanho' => $this->tamanho,
            // Nunca expõe o caminho físico do disco privado — só a rota
            // autenticada que passa pela DocumentoArquivoPolicy::download.
            'url_download' => route('api.v1.documentos.arquivos.baixar', $this->id),
        ];
    }
}
