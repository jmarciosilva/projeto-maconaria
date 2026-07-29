<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\PaginaInstitucional;
use Illuminate\View\View;

final class PaginaInstitucionalController extends Controller
{
    public function mostrar(string $slug): View
    {
        $pagina = PaginaInstitucional::query()
            ->publicado()
            ->where('slug', $slug)
            ->firstOrFail();

        return view('site.pagina-institucional', compact('pagina'));
    }
}
