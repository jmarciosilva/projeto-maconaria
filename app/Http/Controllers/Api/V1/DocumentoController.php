<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SalvarDocumentoEntregaRequest;
use App\Http\Resources\Api\V1\DocumentoAtividadeResource;
use App\Models\DocumentoArquivo;
use App\Models\DocumentoAtividade;
use App\Support\Documentos\EnviadorDeEntrega;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Documentos e trabalhos não têm conteúdo público — todas as rotas exigem
 * token (auth:sanctum) e a mesma permissão já usada no painel
 * ('documentos.visualizar'/'documentos.enviar'), verificada em cada
 * FormRequest reaproveitado do Admin.
 */
final class DocumentoController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('documentos.visualizar');

        $atividades = DocumentoAtividade::query()
            ->latest('created_at')
            ->paginate(20);

        return DocumentoAtividadeResource::collection($atividades);
    }

    public function show(DocumentoAtividade $atividade): DocumentoAtividadeResource
    {
        $this->authorize('documentos.visualizar');

        $atividade->load(['entregas' => fn ($query) => $query
            ->where('usuario_id', auth('sanctum')->id())
            ->with(['arquivos', 'avaliacao'])]);

        return new DocumentoAtividadeResource($atividade);
    }

    public function enviarEntrega(SalvarDocumentoEntregaRequest $request, DocumentoAtividade $atividade, EnviadorDeEntrega $enviador): JsonResponse
    {
        $entrega = $enviador->enviar(
            $atividade,
            $request->user(),
            $request->string('titulo')->value(),
            $request->input('descricao'),
            $request->file('arquivos', []),
        );

        return response()->json(['mensagem' => 'Entrega enviada com sucesso.', 'entrega_id' => $entrega->id], 201);
    }

    public function baixarArquivo(DocumentoArquivo $arquivo): StreamedResponse
    {
        $this->authorize('download', $arquivo);

        return Storage::disk('local')->download($arquivo->caminho, $arquivo->nome_original);
    }
}
