<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SalvarVisitanteChancelariaRequest;
use App\Models\ChancelariaVisitante;
use App\Models\Evento;
use App\Support\RegistradorDeAuditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class ChancelariaVisitanteController extends Controller
{
    public function index(): View
    {
        $this->authorize('chancelaria.visualizar');

        $visitantes = ChancelariaVisitante::query()
            ->with('evento')
            ->latest()
            ->paginate(20);

        return view('admin.chancelaria.visitantes.index', compact('visitantes'));
    }

    public function create(): View
    {
        $this->authorize('chancelaria.criar');

        return view('admin.chancelaria.visitantes.create', $this->dadosFormulario());
    }

    public function store(SalvarVisitanteChancelariaRequest $request): RedirectResponse
    {
        $visitante = DB::transaction(function () use ($request): ChancelariaVisitante {
            $visitante = ChancelariaVisitante::create([
                ...$request->validated(),
                'registrado_por_id' => $request->user()->id,
            ]);

            RegistradorDeAuditoria::registrar('criar', 'chancelaria', 'ChancelariaVisitante', $visitante->id);

            return $visitante;
        });

        return redirect()
            ->route('admin.chancelaria.visitantes.edit', $visitante)
            ->with('sucesso', 'Visitante registrado com sucesso.');
    }

    public function edit(ChancelariaVisitante $visitante): View
    {
        $this->authorize('chancelaria.editar');

        return view('admin.chancelaria.visitantes.edit', [
            ...$this->dadosFormulario(),
            'visitante' => $visitante,
        ]);
    }

    public function update(SalvarVisitanteChancelariaRequest $request, ChancelariaVisitante $visitante): RedirectResponse
    {
        DB::transaction(function () use ($request, $visitante): void {
            $visitante->fill($request->validated())->save();

            RegistradorDeAuditoria::registrar('editar', 'chancelaria', 'ChancelariaVisitante', $visitante->id);
        });

        return back()->with('sucesso', 'Visitante atualizado com sucesso.');
    }

    public function destroy(ChancelariaVisitante $visitante): RedirectResponse
    {
        $this->authorize('chancelaria.editar');

        $visitante->delete();

        RegistradorDeAuditoria::registrar('excluir', 'chancelaria', 'ChancelariaVisitante', $visitante->id);

        return redirect()
            ->route('admin.chancelaria.visitantes.index')
            ->with('sucesso', 'Visitante removido com sucesso.');
    }

    private function dadosFormulario(): array
    {
        return [
            'eventos' => Evento::query()->orderByDesc('inicio_em')->limit(50)->pluck('titulo', 'id'),
        ];
    }
}
