<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site;

use App\Enums\StatusMuralGaleria;
use App\Enums\TipoReacaoMural;
use App\Http\Controllers\Controller;
use App\Models\MuralPublicacao;
use App\Support\RegistradorDeAuditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

final class MuralInteracaoController extends Controller
{
    public function comentar(Request $request, MuralPublicacao $publicacao): RedirectResponse
    {
        abort_unless($publicacao->status === StatusMuralGaleria::PUBLICADO, 404);

        $dados = $request->validate([
            'comentario' => ['required', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($request, $publicacao, $dados): void {
            $comentario = $publicacao->comentarios()->create([
                ...$dados,
                'usuario_id' => $request->user()->id,
                'aprovado' => $request->user()->can('mural.moderar'),
                'aprovado_em' => $request->user()->can('mural.moderar') ? now() : null,
            ]);

            RegistradorDeAuditoria::registrar('comentar-home', 'mural', 'MuralComentario', $comentario->id);
        });

        return back()->with('sucesso', 'Comentário enviado para moderação.');
    }

    public function reagir(Request $request, MuralPublicacao $publicacao): RedirectResponse
    {
        abort_unless($publicacao->status === StatusMuralGaleria::PUBLICADO, 404);

        $dados = $request->validate([
            'tipo' => ['required', Rule::enum(TipoReacaoMural::class)],
        ]);

        DB::transaction(function () use ($request, $publicacao, $dados): void {
            $publicacao->reacoes()->updateOrCreate(
                ['usuario_id' => $request->user()->id, 'tipo' => $dados['tipo']],
                ['usuario_id' => $request->user()->id, 'tipo' => $dados['tipo']],
            );

            RegistradorDeAuditoria::registrar('reagir-home', 'mural', 'MuralPublicacao', $publicacao->id);
        });

        return back()->with('sucesso', 'Reação registrada.');
    }
}
