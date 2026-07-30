<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\StatusEvento;
use App\Enums\TipoEvento;
use App\Enums\VisibilidadeEvento;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SalvarEventoRequest;
use App\Models\Evento;
use App\Support\RegistradorDeAuditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class EventoController extends Controller
{
    public function index(): View
    {
        $this->authorize('eventos.visualizar');

        $eventos = Evento::query()
            ->withCount('confirmacoesAtivas')
            ->orderBy('inicio_em')
            ->paginate(20);

        return view('admin.eventos.index', compact('eventos'));
    }

    public function calendario(): View
    {
        $this->authorize('eventos.visualizar');

        $inicio = now()->startOfMonth();
        $fim = now()->addMonths(2)->endOfMonth();

        $eventos = Evento::query()
            ->whereBetween('inicio_em', [$inicio, $fim])
            ->orderBy('inicio_em')
            ->get()
            ->groupBy(fn (Evento $evento): string => $evento->inicio_em->format('Y-m-d'));

        return view('admin.eventos.calendario', compact('eventos', 'inicio', 'fim'));
    }

    public function create(): View
    {
        $this->authorize('eventos.criar');

        return view('admin.eventos.create', $this->dadosFormulario());
    }

    public function store(SalvarEventoRequest $request): RedirectResponse
    {
        $evento = DB::transaction(function () use ($request): Evento {
            $dados = $request->validated();
            $dados['autor_id'] = $request->user()->id;

            $evento = Evento::create($dados);

            RegistradorDeAuditoria::registrar('criar', 'eventos', 'Evento', $evento->id);

            return $evento;
        });

        return redirect()
            ->route('admin.eventos.edit', $evento)
            ->with('sucesso', 'Evento cadastrado com sucesso.');
    }

    public function edit(Evento $evento): View
    {
        $this->authorize('eventos.editar');

        $evento->load(['confirmacoesAtivas.usuario']);

        return view('admin.eventos.edit', [
            ...$this->dadosFormulario(),
            'evento' => $evento,
        ]);
    }

    public function update(SalvarEventoRequest $request, Evento $evento): RedirectResponse
    {
        DB::transaction(function () use ($request, $evento): void {
            $dadosAnteriores = $evento->only(['titulo', 'slug', 'tipo', 'status', 'visibilidade', 'inicio_em']);

            $evento->fill($request->validated())->save();

            RegistradorDeAuditoria::registrar(
                acao: 'editar',
                modulo: 'eventos',
                entidade: 'Evento',
                entidadeId: $evento->id,
                dadosAnteriores: $dadosAnteriores,
                dadosNovos: $evento->only(['titulo', 'slug', 'tipo', 'status', 'visibilidade', 'inicio_em']),
            );
        });

        return redirect()
            ->route('admin.eventos.edit', $evento)
            ->with('sucesso', 'Evento atualizado com sucesso.');
    }

    public function destroy(Evento $evento): RedirectResponse
    {
        $this->authorize('eventos.excluir');

        $evento->delete();

        RegistradorDeAuditoria::registrar('excluir', 'eventos', 'Evento', $evento->id);

        return redirect()
            ->route('admin.eventos.index')
            ->with('sucesso', 'Evento removido com sucesso.');
    }

    private function dadosFormulario(): array
    {
        return [
            'tipos' => collect(TipoEvento::cases())->mapWithKeys(fn (TipoEvento $tipo) => [$tipo->value => $tipo->rotulo()]),
            'statusDisponiveis' => collect(StatusEvento::cases())->mapWithKeys(fn (StatusEvento $status) => [$status->value => $status->rotulo()]),
            'visibilidades' => collect(VisibilidadeEvento::cases())->mapWithKeys(fn (VisibilidadeEvento $visibilidade) => [$visibilidade->value => $visibilidade->rotulo()]),
        ];
    }
}
