<?php

declare(strict_types=1);

namespace App\Http\Controllers\AreaRestrita;

use App\Http\Controllers\Controller;
use App\Http\Requests\AreaRestrita\ConfirmarPresencaEventoRequest;
use App\Models\Evento;
use App\Support\Eventos\ConfirmadorPresencaEvento;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class EventoController extends Controller
{
    public function index(): View
    {
        $eventos = Evento::query()
            ->with(['confirmacoes' => fn ($query) => $query->where('usuario_id', auth()->id())])
            ->visivelNaAreaRestrita()
            ->futuro()
            ->orderBy('inicio_em')
            ->paginate(12);

        return view('area-restrita.eventos.index', compact('eventos'));
    }

    public function mostrar(Evento $evento): View
    {
        abort_unless($evento->status->value === 'publicado', 404);

        $evento->load(['confirmacoes' => fn ($query) => $query->where('usuario_id', auth()->id())]);

        return view('area-restrita.eventos.mostrar', compact('evento'));
    }

    public function confirmar(ConfirmarPresencaEventoRequest $request, Evento $evento, ConfirmadorPresencaEvento $confirmador): RedirectResponse
    {
        $confirmador->confirmar($evento, $request->user(), $request->input('observacao'));

        return back()->with('sucesso', 'Presença confirmada com sucesso.');
    }

    public function cancelarConfirmacao(Evento $evento, ConfirmadorPresencaEvento $confirmador): RedirectResponse
    {
        $confirmador->cancelar($evento, auth()->user());

        return back()->with('sucesso', 'Confirmação de presença cancelada.');
    }
}
