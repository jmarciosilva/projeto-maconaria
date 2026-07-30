<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\NoticiaResource;
use App\Models\Noticia;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Mesma visibilidade do site público (Site\NoticiaController): apenas
 * notícias publicadas e públicas. Notícias restritas ainda não têm uma
 * superfície de leitura fora do painel administrativo (não existe uma
 * "área restrita" de notícias na web) — por isso a API não introduz esse
 * comportamento agora, para não inventar uma regra de visibilidade sem um
 * equivalente já testado. Ver docs/API-FUTURA.md.
 */
final class NoticiaController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $noticias = Noticia::query()
            ->with(['categoria', 'tags'])
            ->publicaNoSite()
            ->latest('publicado_em')
            ->paginate(12);

        return NoticiaResource::collection($noticias);
    }

    public function show(string $slug): NoticiaResource
    {
        $noticia = Noticia::query()
            ->with(['categoria', 'tags'])
            ->publicaNoSite()
            ->where('slug', $slug)
            ->firstOrFail();

        return new NoticiaResource($noticia);
    }
}
