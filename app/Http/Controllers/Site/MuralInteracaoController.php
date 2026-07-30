<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site;

use App\Enums\TipoReacaoMural;
use App\Http\Controllers\Controller;
use App\Models\MuralPublicacao;
use App\Support\Mural\InteracaoMuralService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class MuralInteracaoController extends Controller
{
    public function comentar(Request $request, MuralPublicacao $publicacao, InteracaoMuralService $servico): RedirectResponse
    {
        $dados = $request->validate([
            'comentario' => ['required', 'string', 'max:1000'],
        ]);

        $servico->comentar($publicacao, $request->user(), $dados['comentario']);

        return back()->with('sucesso', 'Comentário enviado para moderação.');
    }

    public function reagir(Request $request, MuralPublicacao $publicacao, InteracaoMuralService $servico): RedirectResponse
    {
        $dados = $request->validate([
            'tipo' => ['required', Rule::enum(TipoReacaoMural::class)],
        ]);

        $servico->reagir($publicacao, $request->user(), TipoReacaoMural::from($dados['tipo']));

        return back()->with('sucesso', 'Reação registrada.');
    }
}
