<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\StatusComunicadoChancelaria;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SalvarComunicadoChancelariaRequest;
use App\Models\ChancelariaComunicado;
use App\Support\Chancelaria\ProcessadorConteudoComunicado;
use App\Support\RegistradorDeAuditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class ChancelariaComunicadoController extends Controller
{
    public function index(): View
    {
        $this->authorize('chancelaria.visualizar');

        $comunicados = ChancelariaComunicado::query()
            ->latest()
            ->paginate(20);

        return view('admin.chancelaria.comunicados.index', compact('comunicados'));
    }

    public function create(): View
    {
        $this->authorize('chancelaria.criar');

        return view('admin.chancelaria.comunicados.create', $this->dadosFormulario());
    }

    public function store(SalvarComunicadoChancelariaRequest $request): RedirectResponse
    {
        $comunicado = DB::transaction(function () use ($request): ChancelariaComunicado {
            $dados = $request->validated();
            $dados['conteudo'] = ProcessadorConteudoComunicado::prepararParaSalvar($request->input('conteudo'));
            $dados['autor_id'] = $request->user()->id;

            if ($dados['status'] === StatusComunicadoChancelaria::PUBLICADO->value) {
                $dados['publicado_em'] = now();
            }

            $comunicado = ChancelariaComunicado::create($dados);

            RegistradorDeAuditoria::registrar('criar', 'chancelaria', 'ChancelariaComunicado', $comunicado->id);

            return $comunicado;
        });

        return redirect()
            ->route('admin.chancelaria.comunicados.edit', $comunicado)
            ->with('sucesso', 'Comunicado cadastrado com sucesso.');
    }

    public function edit(ChancelariaComunicado $comunicado): View
    {
        $this->authorize('chancelaria.editar');

        return view('admin.chancelaria.comunicados.edit', [
            ...$this->dadosFormulario(),
            'comunicado' => $comunicado,
        ]);
    }

    public function update(SalvarComunicadoChancelariaRequest $request, ChancelariaComunicado $comunicado): RedirectResponse
    {
        DB::transaction(function () use ($request, $comunicado): void {
            $dados = $request->validated();
            $dados['conteudo'] = ProcessadorConteudoComunicado::prepararParaSalvar($request->input('conteudo'));

            if ($dados['status'] === StatusComunicadoChancelaria::PUBLICADO->value && ! $comunicado->publicado_em) {
                $dados['publicado_em'] = now();
            }

            $comunicado->fill($dados)->save();

            RegistradorDeAuditoria::registrar('editar', 'chancelaria', 'ChancelariaComunicado', $comunicado->id);
        });

        return back()->with('sucesso', 'Comunicado atualizado com sucesso.');
    }

    public function destroy(ChancelariaComunicado $comunicado): RedirectResponse
    {
        $this->authorize('chancelaria.editar');

        $comunicado->delete();

        RegistradorDeAuditoria::registrar('excluir', 'chancelaria', 'ChancelariaComunicado', $comunicado->id);

        return redirect()
            ->route('admin.chancelaria.comunicados.index')
            ->with('sucesso', 'Comunicado removido com sucesso.');
    }

    private function dadosFormulario(): array
    {
        return [
            'statusDisponiveis' => collect(StatusComunicadoChancelaria::cases())->mapWithKeys(fn (StatusComunicadoChancelaria $status) => [$status->value => $status->rotulo()]),
        ];
    }
}
