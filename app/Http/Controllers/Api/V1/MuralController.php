<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\StatusMuralGaleria;
use App\Enums\TipoReacaoMural;
use App\Enums\VisibilidadeMuralGaleria;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ComentarMuralRequest;
use App\Http\Requests\Api\V1\ReagirMuralRequest;
use App\Http\Resources\Api\V1\MuralPublicacaoResource;
use App\Models\MuralPublicacao;
use App\Support\Mural\InteracaoMuralService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Mesma visibilidade do feed da home (scopePublico): não existe, hoje, uma
 * "área restrita" de mural distinta — publicações restritas só aparecem no
 * painel administrativo. A API não inventa esse comportamento (ver
 * NoticiaController e docs/API-FUTURA.md para a mesma decisão).
 */
final class MuralController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $publicacoes = $this->consulta($request)
            ->latest('publicado_em')
            ->paginate(12);

        return MuralPublicacaoResource::collection($publicacoes);
    }

    public function show(Request $request, MuralPublicacao $publicacao): MuralPublicacaoResource
    {
        abort_unless(
            $publicacao->status === StatusMuralGaleria::PUBLICADO && $publicacao->visibilidade === VisibilidadeMuralGaleria::PUBLICA,
            404,
        );

        $publicacao = $this->consulta($request)->whereKey($publicacao->id)->firstOrFail();

        return new MuralPublicacaoResource($publicacao);
    }

    public function comentar(ComentarMuralRequest $request, MuralPublicacao $publicacao, InteracaoMuralService $servico): JsonResponse
    {
        $servico->comentar($publicacao, $request->user('sanctum'), $request->string('comentario')->value());

        return response()->json(['mensagem' => 'Comentário enviado para moderação.'], 201);
    }

    public function reagir(ReagirMuralRequest $request, MuralPublicacao $publicacao, InteracaoMuralService $servico): JsonResponse
    {
        $servico->reagir($publicacao, $request->user('sanctum'), TipoReacaoMural::from($request->string('tipo')->value()));

        return response()->json(['mensagem' => 'Reação registrada.'], 201);
    }

    /**
     * @return Builder<MuralPublicacao>
     */
    private function consulta(Request $request)
    {
        $usuario = $request->user('sanctum');

        $query = MuralPublicacao::query()
            ->with('autor')
            ->withCount(['comentarios as comentarios_aprovados_count' => fn ($query) => $query->where('aprovado', true)])
            ->withCount('reacoes')
            ->publico();

        if ($usuario) {
            $query->with(['reacoes' => fn ($query) => $query->where('usuario_id', $usuario->id)]);
        }

        return $query;
    }
}
