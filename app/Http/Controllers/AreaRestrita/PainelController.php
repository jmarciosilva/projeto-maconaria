<?php

namespace App\Http\Controllers\AreaRestrita;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use Illuminate\View\View;

final class PainelController extends Controller
{
    public function index(): View
    {
        $proximosEventos = Evento::query()
            ->visivelNaAreaRestrita()
            ->futuro()
            ->orderBy('inicio_em')
            ->limit(5)
            ->get();

        return view('area-restrita.painel', compact('proximosEventos'));
    }
}
