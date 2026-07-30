<?php

declare(strict_types=1);

namespace App\Support\Eventos;

use App\Enums\StatusConfirmacaoPresenca;
use App\Models\Evento;
use App\Models\EventoConfirmacaoPresenca;
use App\Models\User;
use App\Support\RegistradorDeAuditoria;
use Illuminate\Support\Facades\DB;

/**
 * Confirmação de presença é usada tanto pela área restrita (web) quanto pela
 * API mobile (Fase 12) — centralizada aqui para as duas nunca divergirem na
 * regra de vagas/transação (ver docs/API-FUTURA.md).
 */
final class ConfirmadorPresencaEvento
{
    public function confirmar(Evento $evento, User $usuario, ?string $observacao): EventoConfirmacaoPresenca
    {
        abort_unless($evento->aceitaConfirmacao(), 422, 'Este evento não aceita confirmação de presença no momento.');

        return DB::transaction(function () use ($evento, $usuario, $observacao): EventoConfirmacaoPresenca {
            $confirmacaoExistente = $evento->confirmacoes()
                ->where('usuario_id', $usuario->id)
                ->first();

            // A vaga só é consumida por confirmações ativas; reativar a própria
            // confirmação cancelada precisa passar pela mesma regra de lotação.
            if (
                (! $confirmacaoExistente || $confirmacaoExistente->status === StatusConfirmacaoPresenca::CANCELADO)
                && ! $evento->possuiVagaDisponivel()
            ) {
                abort(422, 'Não há vagas disponíveis para este evento.');
            }

            $confirmacao = $evento->confirmacoes()->updateOrCreate(
                ['usuario_id' => $usuario->id],
                [
                    'status' => StatusConfirmacaoPresenca::CONFIRMADO,
                    'observacao' => $observacao,
                ],
            );

            RegistradorDeAuditoria::registrar('confirmar-presenca', 'eventos', 'EventoConfirmacaoPresenca', $confirmacao->id);

            return $confirmacao;
        });
    }

    public function cancelar(Evento $evento, User $usuario): EventoConfirmacaoPresenca
    {
        $confirmacao = $evento->confirmacoes()
            ->where('usuario_id', $usuario->id)
            ->firstOrFail();

        $confirmacao->update(['status' => StatusConfirmacaoPresenca::CANCELADO]);

        RegistradorDeAuditoria::registrar('cancelar-presenca', 'eventos', 'EventoConfirmacaoPresenca', $confirmacao->id);

        return $confirmacao;
    }
}
