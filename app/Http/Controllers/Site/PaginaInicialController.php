<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\CarrosselItem;
use App\Models\Evento;
use App\Models\GaleriaAlbum;
use App\Models\MuralPublicacao;
use App\Models\Noticia;
use App\Models\PaginaInstitucional;
use Illuminate\View\View;

final class PaginaInicialController extends Controller
{
    /**
     * Slugs realmente usados pela administração da Loja ao cadastrar cada
     * página pelo painel — nem sempre idênticos ao valor original do
     * PaginaInstitucionalSeeder, por isso mantidos como constante única,
     * também referenciada pelas rotas fixas em routes/web.php.
     *
     * @var array<int, string>
     */
    private const SLUGS_INSTITUCIONAIS_DA_HOME = [
        'sobre-nos',
        'nossa-historia',
        'o-que-e-maconaria',
        'maconaria-para-jovens',
        'mudando-o-cidadao',
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

        // Limite 5: a primeira vira a matéria principal e as demais formam a
        // coluna de manchetes secundárias (ver resources/views/site/home.blade.php).
        $noticiasEmDestaque = Noticia::query()
            ->with('categoria')
            ->publicaNoSite()
            ->destaque()
            ->latest('publicado_em')
            ->limit(5)
            ->get();

        $proximosEventos = Evento::query()
            ->publicoNoSite()
            ->futuro()
            ->orderBy('inicio_em')
            ->limit(3)
            ->get();

        $publicacoesMural = MuralPublicacao::query()
            ->with([
                'autor',
                'comentarios' => fn ($query) => $query->where('aprovado', true)->with('usuario')->latest('created_at'),
            ])
            ->withCount([
                'reacoes',
                'comentarios as comentarios_aprovados_count' => fn ($query) => $query->where('aprovado', true),
            ])
            ->publico()
            ->latest('publicado_em')
            ->limit(3)
            ->get();

        $albunsGaleria = GaleriaAlbum::query()
            ->with('fotografias')
            ->withCount('fotografias')
            ->publico()
            ->latest('publicado_em')
            ->limit(3)
            ->get();

        return view('site.home', compact(
            'itensCarrossel',
            'paginasInstitucionais',
            'noticiasEmDestaque',
            'proximosEventos',
            'publicacoesMural',
            'albunsGaleria',
        ));
    }
}
