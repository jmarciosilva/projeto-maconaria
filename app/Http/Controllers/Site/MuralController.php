<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site;

use App\Enums\StatusMuralGaleria;
use App\Enums\VisibilidadeMuralGaleria;
use App\Http\Controllers\Controller;
use App\Models\MuralPublicacao;
use Illuminate\View\View;

/**
 * Mural é sempre público para visualização — só interagir (comentar/curtir)
 * exige login, tratado por MuralInteracaoController (rotas sob middleware
 * "auth" em routes/web.php).
 */
final class MuralController extends Controller
{
    public function index(): View
    {
        $publicacoes = MuralPublicacao::query()
            ->with('autor')
            ->withCount([
                'reacoes',
                'comentarios as comentarios_aprovados_count' => fn ($query) => $query->where('aprovado', true),
            ])
            ->publico()
            ->latest('publicado_em')
            ->paginate(12);

        return view('site.mural.index', compact('publicacoes'));
    }

    public function mostrar(MuralPublicacao $publicacao): View
    {
        abort_unless(
            $publicacao->status === StatusMuralGaleria::PUBLICADO && $publicacao->visibilidade === VisibilidadeMuralGaleria::PUBLICA,
            404,
        );

        $publicacao->load([
            'autor',
            'comentarios' => fn ($query) => $query->where('aprovado', true)->with('usuario')->latest('created_at'),
        ])->loadCount('reacoes');

        return view('site.mural.mostrar', compact('publicacao'));
    }
}
