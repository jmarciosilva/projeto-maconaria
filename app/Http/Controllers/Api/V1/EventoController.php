<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AreaRestrita\ConfirmarPresencaEventoRequest;
use App\Http\Resources\Api\V1\EventoResource;
use App\Models\Evento;
use App\Support\Eventos\ConfirmadorPresencaEvento;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Eventos restritos (visibilidade "restrita") só aparecem para usuários
 * autenticados — mesma regra já aplicada entre Site\EventoController
 * (público) e AreaRestrita\EventoController (autenticado) na web.
 */
final class EventoController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $eventos = $this->consulta($request)
            ->futuro()
            ->orderBy('inicio_em')
            ->paginate(12);

        return EventoResource::collection($eventos);
    }

    public function show(Request $request, string $slug): EventoResource
    {
        $evento = $this->consulta($request)
            ->where('slug', $slug)
            ->firstOrFail();

        return new EventoResource($evento);
    }

    /**
     * Calendário público de eventos e sessões, agrupado por dia — mesma
     * regra de visibilidade de index()/show().
     */
    public function calendario(Request $request): JsonResponse
    {
        $inicio = now()->startOfMonth();
        $fim = now()->addMonths(2)->endOfMonth();

        $eventos = $this->consulta($request)
            ->whereBetween('inicio_em', [$inicio, $fim])
            ->orderBy('inicio_em')
            ->get()
            ->groupBy(fn (Evento $evento): string => $evento->inicio_em->format('Y-m-d'))
            ->map(fn ($eventosDoDia) => EventoResource::collection($eventosDoDia)->resolve());

        return response()->json([
            'inicio' => $inicio->toDateString(),
            'fim' => $fim->toDateString(),
            'dias' => $eventos,
        ]);
    }

    public function confirmar(ConfirmarPresencaEventoRequest $request, Evento $evento, ConfirmadorPresencaEvento $confirmador): JsonResponse
    {
        $confirmador->confirmar($evento, $request->user('sanctum'), $request->input('observacao'));

        return response()->json(['mensagem' => 'Presença confirmada com sucesso.']);
    }

    public function cancelarConfirmacao(Request $request, Evento $evento, ConfirmadorPresencaEvento $confirmador): JsonResponse
    {
        $confirmador->cancelar($evento, $request->user('sanctum'));

        return response()->json(['mensagem' => 'Confirmação de presença cancelada.']);
    }

    /**
     * As rotas de listagem/detalhe não exigem token (guest vê só eventos
     * públicos), mas usam o guard "sanctum" explicitamente para detectar um
     * usuário autenticado quando o token é enviado — o guard padrão da
     * aplicação é "web" (sessão), que nunca reconhece um Bearer token.
     *
     * @return Builder<Evento>
     */
    private function consulta(Request $request)
    {
        $usuario = $request->user('sanctum');
        $query = Evento::query();

        if ($usuario) {
            $query->with(['confirmacoes' => fn ($query) => $query->where('usuario_id', $usuario->id)])
                ->visivelNaAreaRestrita();
        } else {
            $query->publicoNoSite();
        }

        return $query;
    }
}
