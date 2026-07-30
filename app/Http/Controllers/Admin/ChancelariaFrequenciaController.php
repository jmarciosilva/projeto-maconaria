<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\StatusFrequencia;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SalvarFrequenciaChancelariaRequest;
use App\Models\ChancelariaFrequencia;
use App\Models\Evento;
use App\Models\Irmao;
use App\Support\RegistradorDeAuditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class ChancelariaFrequenciaController extends Controller
{
    public function selecionarEvento(): View
    {
        $this->authorize('chancelaria.visualizar');

        $eventos = Evento::query()
            ->orderByDesc('inicio_em')
            ->limit(30)
            ->get();

        return view('admin.chancelaria.frequencias.selecionar-evento', compact('eventos'));
    }

    public function edit(Evento $evento): View
    {
        $this->authorize('chancelaria.editar');

        $irmaos = Irmao::query()->orderBy('nome_completo')->get();
        $frequencias = ChancelariaFrequencia::query()
            ->where('evento_id', $evento->id)
            ->get()
            ->keyBy('irmao_id');

        return view('admin.chancelaria.frequencias.edit', [
            'evento' => $evento,
            'irmaos' => $irmaos,
            'frequencias' => $frequencias,
            'statusDisponiveis' => collect(StatusFrequencia::cases())->mapWithKeys(fn (StatusFrequencia $status) => [$status->value => $status->rotulo()]),
        ]);
    }

    public function update(SalvarFrequenciaChancelariaRequest $request, Evento $evento): RedirectResponse
    {
        DB::transaction(function () use ($request, $evento): void {
            foreach ($request->input('frequencias', []) as $irmaoId => $dados) {
                if (blank($dados['status'] ?? null)) {
                    ChancelariaFrequencia::query()
                        ->where('evento_id', $evento->id)
                        ->where('irmao_id', $irmaoId)
                        ->delete();

                    continue;
                }

                ChancelariaFrequencia::updateOrCreate(
                    [
                        'evento_id' => $evento->id,
                        'irmao_id' => (int) $irmaoId,
                    ],
                    [
                        'registrado_por_id' => $request->user()->id,
                        'status' => $dados['status'],
                        'observacao' => $dados['observacao'] ?? null,
                    ],
                );
            }

            RegistradorDeAuditoria::registrar('registrar-frequencia', 'chancelaria', 'Evento', $evento->id);
        });

        return back()->with('sucesso', 'Frequência registrada com sucesso.');
    }
}
