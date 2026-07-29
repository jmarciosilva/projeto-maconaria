<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\CarrosselItem;
use Illuminate\View\View;

final class PaginaInicialController extends Controller
{
    public function index(): View
    {
        $itensCarrossel = CarrosselItem::query()->ativo()->vigente()->ordenado()->get();

        return view('site.home', compact('itensCarrossel'));
    }
}
