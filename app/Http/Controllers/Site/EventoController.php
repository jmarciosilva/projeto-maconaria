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
}
