<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FecharPeriodoTesourariaRequest;
use App\Models\TesourariaFechamento;
use App\Support\RegistradorDeAuditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class TesourariaFechamentoController extends Controller
{
    public function index(): View
    {
        $this->authorize('tesouraria.visualizar');

        $fechamentos = TesourariaFechamento::query()->latest('fechado_em')->paginate(20);

        return view('admin.tesouraria.fechamentos.index', compact('fechamentos'));
    }

    public function store(FecharPeriodoTesourariaRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $fechamento = TesourariaFechamento::create([
                ...$request->validated(),
                'fechado_por_id' => $request->user()->id,
                'fechado_em' => now(),
            ]);

            RegistradorDeAuditoria::registrar('fechar-periodo', 'tesouraria', 'TesourariaFechamento', $fechamento->id);
        });

        return back()->with('sucesso', 'Período fechado com sucesso.');
    }
}
