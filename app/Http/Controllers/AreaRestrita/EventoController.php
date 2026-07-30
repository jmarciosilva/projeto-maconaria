<?php

declare(strict_types=1);

namespace App\Http\Controllers\AreaRestrita;

use App\Enums\StatusConfirmacaoPresenca;
use App\Http\Controllers\Controller;
use App\Http\Requests\AreaRestrita\ConfirmarPresencaEventoRequest;
use App\Models\Evento;
use App\Support\RegistradorDeAuditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class EventoController extends Controller
{
    public function index(): View
    {
        $eventos = Evento::query()
            ->with(['confirmacoes' => fn ($query) => $query->where('usuario_id', auth()->id())])
            ->visivelNaAreaRestrita()
            ->futuro()
            ->orderBy('inicio_em')
            ->paginate(12);

        return view('area-restrita.eventos.index', compact('eventos'));
    }

    public function mostrar(Evento $evento): View
    {
        abort_unless($evento->status->value === 'publicado', 404);

        $evento->load(['confirmacoes' => fn ($query) => $query->where('usuario_id', auth()->id())]);

        return view('area-restrita.eventos.mostrar', compact('evento'));
    }

    public function confirmar(ConfirmarPresencaEventoRequest $request, Evento $evento): RedirectResponse
    {
        abort_unless($evento->aceitaConfirmacao(), 422, 'Este evento não aceita confirmação de presença no momento.');

        DB::transaction(function () use ($request, $evento): void {
            $confirmacaoExistente = $evento->confirmacoes()
                ->where('usuario_id', $request->user()->id)
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
                ['usuario_id' => $request->user()->id],
                [
                    'status' => StatusConfirmacaoPresenca::CONFIRMADO,
                    'observacao' => $request->input('observacao'),
                ],
            );

            RegistradorDeAuditoria::registrar('confirmar-presenca', 'eventos', 'EventoConfirmacaoPresenca', $confirmacao->id);
        });

        return back()->with('sucesso', 'Presença confirmada com sucesso.');
    }

    public function cancelarConfirmacao(Evento $evento): RedirectResponse
    {
        $confirmacao = $evento->confirmacoes()
            ->where('usuario_id', auth()->id())
            ->firstOrFail();

        $confirmacao->update(['status' => StatusConfirmacaoPresenca::CANCELADO]);

        RegistradorDeAuditoria::registrar('cancelar-presenca', 'eventos', 'EventoConfirmacaoPresenca', $confirmacao->id);

        return back()->with('sucesso', 'Confirmação de presença cancelada.');
    }
}
