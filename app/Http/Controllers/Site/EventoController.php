<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use Illuminate\View\View;

final class EventoController extends Controller
{
    public function index(): View
    {
        $eventos = Evento::query()
            ->publicoNoSite()
            ->futuro()
            ->orderBy('inicio_em')
            ->paginate(12);

        return view('site.eventos.index', compact('eventos'));
    }

    public function mostrar(string $slug): View
    {
        $evento = Evento::query()
            ->publicoNoSite()
            ->where('slug', $slug)
            ->firstOrFail();

        return view('site.eventos.mostrar', compact('evento'));
    }

    /**
     * Calendário público de eventos e sessões: nunca exige login para
     * visualizar — mostra apenas eventos públicos, mesma regra do index().
     */
    public function calendario(): View
    {
        $inicio = now()->startOfMonth();
        $fim = now()->addMonths(2)->endOfMonth();

        $eventos = Evento::query()
            ->publicoNoSite()
            ->whereBetween('inicio_em', [$inicio, $fim])
            ->orderBy('inicio_em')
            ->get()
            ->groupBy(fn (Evento $evento): string => $evento->inicio_em->format('Y-m-d'));

        return view('site.eventos.calendario', compact('eventos', 'inicio', 'fim'));
    }
}
