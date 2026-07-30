<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Noticia;
use Illuminate\View\View;

final class NoticiaController extends Controller
{
    public function index(): View
    {
        $noticias = Noticia::query()
            ->with('categoria')
            ->publicaNoSite()
            ->latest('publicado_em')
            ->paginate(12);

        return view('site.noticias.index', compact('noticias'));
    }

    public function mostrar(string $slug): View
    {
        $noticia = Noticia::query()
            ->with(['categoria', 'tags'])
            ->publicaNoSite()
            ->where('slug', $slug)
            ->firstOrFail();

        return view('site.noticias.mostrar', compact('noticia'));
    }
}
