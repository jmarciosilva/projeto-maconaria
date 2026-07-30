<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\CarrosselItem;
use App\Models\Evento;
use App\Models\Noticia;
use App\Models\PaginaInstitucional;
use Illuminate\View\View;

final class PaginaInicialController extends Controller
{
    /**
     * @var array<int, string>
     */
    private const SLUGS_INSTITUCIONAIS_DA_HOME = [
        'sobre-nos',
        'maconaria',
        'maconaria-jovens',
        'mudar-cidadao',
    ];

    public function index(): View
    {
        $itensCarrossel = CarrosselItem::query()->ativo()->vigente()->ordenado()->get();
        $ordem = array_flip(self::SLUGS_INSTITUCIONAIS_DA_HOME);

        $paginasInstitucionais = PaginaInstitucional::query()
            ->publicado()
            ->whereIn('slug', self::SLUGS_INSTITUCIONAIS_DA_HOME)
            ->get()
            ->sortBy(fn (PaginaInstitucional $pagina): int => $ordem[$pagina->slug] ?? PHP_INT_MAX)
            ->values();

        $noticiasEmDestaque = Noticia::query()
            ->with('categoria')
            ->publicaNoSite()
            ->destaque()
            ->latest('publicado_em')
            ->limit(3)
            ->get();

        $proximosEventos = Evento::query()
            ->publicoNoSite()
            ->futuro()
            ->orderBy('inicio_em')
            ->limit(3)
            ->get();

        return view('site.home', compact('itensCarrossel', 'paginasInstitucionais', 'noticiasEmDestaque', 'proximosEventos'));
    }
}
