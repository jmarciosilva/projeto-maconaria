<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\GaleriaAlbum;
use Illuminate\View\View;

/**
 * Galeria da Loja é sempre pública para visualização — não existe hoje um
 * álbum "restrito a autenticados" (o campo de visibilidade só distingue
 * público/publicado de rascunho, mesma regra usada na home).
 */
final class GaleriaController extends Controller
{
    public function index(): View
    {
        $albuns = GaleriaAlbum::query()
            ->with('fotografias')
            ->withCount('fotografias')
            ->publico()
            ->latest('publicado_em')
            ->paginate(12);

        return view('site.galeria.index', compact('albuns'));
    }

    public function mostrar(string $slug): View
    {
        $album = GaleriaAlbum::query()
            ->with('fotografias')
            ->publico()
            ->where('slug', $slug)
            ->firstOrFail();

        return view('site.galeria.mostrar', compact('album'));
    }
}
