<?php

declare(strict_types=1);

namespace App\Support\Mural;

use App\Enums\StatusMuralGaleria;
use App\Enums\TipoReacaoMural;
use App\Models\MuralComentario;
use App\Models\MuralPublicacao;
use App\Models\MuralReacao;
use App\Models\User;
use App\Support\RegistradorDeAuditoria;
use Illuminate\Support\Facades\DB;

/**
 * Comentar/reagir no mural é usado tanto pela home (web) quanto pela API
 * mobile (Fase 12) — centralizado aqui para as duas nunca divergirem na
 * regra de moderação (comentário de quem modera entra já aprovado).
 */
final class InteracaoMuralService
{
    public function comentar(MuralPublicacao $publicacao, User $usuario, string $comentario): MuralComentario
    {
        abort_unless($publicacao->status === StatusMuralGaleria::PUBLICADO, 404);

        return DB::transaction(function () use ($publicacao, $usuario, $comentario): MuralComentario {
            $registro = $publicacao->comentarios()->create([
                'comentario' => $comentario,
                'usuario_id' => $usuario->id,
                'aprovado' => $usuario->can('mural.moderar'),
                'aprovado_em' => $usuario->can('mural.moderar') ? now() : null,
            ]);

            RegistradorDeAuditoria::registrar('comentar', 'mural', 'MuralComentario', $registro->id);

            return $registro;
        });
    }

    public function reagir(MuralPublicacao $publicacao, User $usuario, TipoReacaoMural $tipo): MuralReacao
    {
        abort_unless($publicacao->status === StatusMuralGaleria::PUBLICADO, 404);

        return DB::transaction(function () use ($publicacao, $usuario, $tipo): MuralReacao {
            $reacao = $publicacao->reacoes()->updateOrCreate(
                ['usuario_id' => $usuario->id, 'tipo' => $tipo->value],
                ['usuario_id' => $usuario->id, 'tipo' => $tipo->value],
            );

            RegistradorDeAuditoria::registrar('reagir', 'mural', 'MuralPublicacao', $publicacao->id);

            return $reacao;
        });
    }
}
