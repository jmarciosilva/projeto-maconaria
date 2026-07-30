<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\StatusFrequencia;
use App\Http\Controllers\Controller;
use App\Models\ChancelariaFrequencia;
use App\Models\ChancelariaVisitante;
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

        $totaisFrequencia = ChancelariaFrequencia::query()
            ->whereBetween('created_at', [$inicio, $fim])
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $eventosRecentes = Evento::query()
            ->withCount(['confirmacoesAtivas'])
            ->where('inicio_em', '<=', now()->addMonth())
            ->latest('inicio_em')
            ->limit(8)
            ->get();

        $visitantesRecentes = ChancelariaVisitante::query()
            ->with('evento')
            ->latest()
            ->limit(8)
            ->get();

        RegistradorDeAuditoria::registrar('visualizar-relatorio', 'chancelaria');

        return view('admin.chancelaria.index', [
            'eventosRecentes' => $eventosRecentes,
            'visitantesRecentes' => $visitantesRecentes,
            'presentes' => (int) ($totaisFrequencia[StatusFrequencia::PRESENTE->value] ?? 0),
            'ausentes' => (int) ($totaisFrequencia[StatusFrequencia::AUSENTE->value] ?? 0),
            'justificados' => (int) ($totaisFrequencia[StatusFrequencia::JUSTIFICADO->value] ?? 0),
        ]);
    }
}
