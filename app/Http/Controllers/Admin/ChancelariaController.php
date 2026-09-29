<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\StatusFrequencia;
use App\Enums\TipoEvento;
use App\Http\Controllers\Controller;
use App\Models\ChancelariaFrequencia;
use App\Models\Evento;
use App\Support\RegistradorDeAuditoria;
use Illuminate\View\View;

final class ChancelariaController extends Controller
{
    public function index(): View
    {
        $this->authorize('chancelaria.visualizar');

        $inicio = now()->subMonths(3)->startOfDay();
        $fim = now()->endOfDay();

        // Critério preservado do comportamento anterior: a janela incide sobre
        // created_at, ou seja, conta os LANÇAMENTOS feitos no período — e não
        // a data em que a sessão ocorreu. Isso importa no trabalho histórico
        // (lançar 2025 hoje conta como lançamento de hoje), por isso a tela
        // rotula os cartões explicitamente.
        $totaisFrequencia = ChancelariaFrequencia::query()
            ->whereBetween('created_at', [$inicio, $fim])
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        // Sessões realizadas no período (pela data da sessão).
        $sessoesNoPeriodo = Evento::query()
            ->where('tipo', TipoEvento::SESSAO->value)
            ->whereBetween('inicio_em', [$inicio, $fim])
            ->count();

        // Somente sessões: eventos comuns (palestras, jantares, eventos
        // públicos) não pertencem ao trabalho da Chancelaria.
        $sessoesRecentes = Evento::query()
            ->where('tipo', TipoEvento::SESSAO->value)
            ->withCount('frequencias')
            ->latest('inicio_em')
            ->limit(6)
            ->get();

        RegistradorDeAuditoria::registrar('visualizar-relatorio', 'chancelaria');

        return view('admin.chancelaria.index', [
            'inicio' => $inicio,
            'fim' => $fim,
            'sessoesNoPeriodo' => $sessoesNoPeriodo,
            'sessoesRecentes' => $sessoesRecentes,
            'presentes' => (int) ($totaisFrequencia[StatusFrequencia::PRESENTE->value] ?? 0),
            'ausentes' => (int) ($totaisFrequencia[StatusFrequencia::AUSENTE->value] ?? 0),
            'justificados' => (int) ($totaisFrequencia[StatusFrequencia::JUSTIFICADO->value] ?? 0),
        ]);
    }
}
