<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\CategoriaRecadoPainel;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SalvarRecadoPainelRequest;
use App\Models\PainelRecado;
use App\Support\RegistradorDeAuditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class PainelRecadoController extends Controller
{
    public function index(): View
    {
        $this->authorize('recados.visualizar');

        $recados = PainelRecado::query()
            ->latest()
            ->paginate(20);

        return view('admin.recados.index', compact('recados'));
    }

    public function create(): View
    {
        $this->authorize('recados.criar');

        return view('admin.recados.create', $this->dadosFormulario());
    }

    public function store(SalvarRecadoPainelRequest $request): RedirectResponse
    {
        $recado = DB::transaction(function () use ($request): PainelRecado {
            $dados = $request->validated();
            $dados['conteudo'] = clean($dados['conteudo'] ?? '', 'institucional');
            $dados['autor_id'] = $request->user()->id;

            if ($dados['ativo'] && empty($dados['publicado_em'])) {
                $dados['publicado_em'] = now();
            }

            $recado = PainelRecado::create($dados);

            RegistradorDeAuditoria::registrar('criar', 'recados', 'PainelRecado', $recado->id);

            return $recado;
        });

        return redirect()
            ->route('admin.recados.edit', $recado)
            ->with('sucesso', 'Recado cadastrado com sucesso.');
    }

    public function edit(PainelRecado $recado): View
    {
        $this->authorize('recados.editar');

        return view('admin.recados.edit', [
            ...$this->dadosFormulario(),
            'recado' => $recado,
        ]);
    }

    public function update(SalvarRecadoPainelRequest $request, PainelRecado $recado): RedirectResponse
    {
        DB::transaction(function () use ($request, $recado): void {
            $dados = $request->validated();
            $dados['conteudo'] = clean($dados['conteudo'] ?? '', 'institucional');

            if ($dados['ativo'] && empty($dados['publicado_em']) && ! $recado->publicado_em) {
                $dados['publicado_em'] = now();
            }

            $recado->fill($dados)->save();

            RegistradorDeAuditoria::registrar('editar', 'recados', 'PainelRecado', $recado->id);
        });

        return back()->with('sucesso', 'Recado atualizado com sucesso.');
    }

    public function destroy(PainelRecado $recado): RedirectResponse
    {
        $this->authorize('recados.editar');

        $recado->delete();

        RegistradorDeAuditoria::registrar('excluir', 'recados', 'PainelRecado', $recado->id);

        return redirect()
            ->route('admin.recados.index')
            ->with('sucesso', 'Recado removido com sucesso.');
    }

    private function dadosFormulario(): array
    {
        return [
            'categoriasDisponiveis' => collect(CategoriaRecadoPainel::cases())->mapWithKeys(fn (CategoriaRecadoPainel $categoria) => [$categoria->value => $categoria->rotulo()]),
        ];
    }
}
