<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AtualizarCarrosselItemRequest;
use App\Http\Requests\Admin\CriarCarrosselItemRequest;
use App\Models\CarrosselItem;
use App\Support\RegistradorDeAuditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

final class CarrosselItemController extends Controller
{
    public function index(): View
    {
        $this->authorize('cms.visualizar');

        $itens = CarrosselItem::query()->ordenado()->get();

        return view('admin.carrossel.index', compact('itens'));
    }

    public function create(): View
    {
        $this->authorize('cms.editar');

        return view('admin.carrossel.create');
    }

    public function store(CriarCarrosselItemRequest $request): RedirectResponse
    {
        $dados = $request->safe()->except(['imagem_desktop', 'imagem_mobile']);
        $dados['ativo'] = $request->boolean('ativo');
        $dados['abrir_em_nova_aba'] = $request->boolean('abrir_em_nova_aba');

        $item = new CarrosselItem($dados);
        $item->imagem_desktop = $request->file('imagem_desktop')->store('carrossel', 'public');

        if ($request->hasFile('imagem_mobile')) {
            $item->imagem_mobile = $request->file('imagem_mobile')->store('carrossel', 'public');
        }

        $item->save();

        RegistradorDeAuditoria::registrar(
            acao: 'criar',
            modulo: 'cms',
            entidade: 'CarrosselItem',
            entidadeId: $item->id,
        );

        return redirect()
            ->route('admin.carrossel.index')
            ->with('sucesso', 'Item do carrossel cadastrado com sucesso.');
    }

    public function edit(CarrosselItem $carrossel): View
    {
        $this->authorize('cms.editar');

        return view('admin.carrossel.edit', ['item' => $carrossel]);
    }

    public function update(AtualizarCarrosselItemRequest $request, CarrosselItem $carrossel): RedirectResponse
    {
        $dados = $request->safe()->except(['imagem_desktop', 'imagem_mobile']);
        $dados['ativo'] = $request->boolean('ativo');
        $dados['abrir_em_nova_aba'] = $request->boolean('abrir_em_nova_aba');

        $carrossel->fill($dados);

        if ($request->hasFile('imagem_desktop')) {
            Storage::disk('public')->delete($carrossel->getOriginal('imagem_desktop'));
            $carrossel->imagem_desktop = $request->file('imagem_desktop')->store('carrossel', 'public');
        }

        if ($request->hasFile('imagem_mobile')) {
            if ($carrossel->getOriginal('imagem_mobile')) {
                Storage::disk('public')->delete($carrossel->getOriginal('imagem_mobile'));
            }

            $carrossel->imagem_mobile = $request->file('imagem_mobile')->store('carrossel', 'public');
        }

        $carrossel->save();

        RegistradorDeAuditoria::registrar(
            acao: 'editar',
            modulo: 'cms',
            entidade: 'CarrosselItem',
            entidadeId: $carrossel->id,
        );

        return redirect()
            ->route('admin.carrossel.index')
            ->with('sucesso', 'Item do carrossel atualizado com sucesso.');
    }

    public function destroy(CarrosselItem $carrossel): RedirectResponse
    {
        $this->authorize('cms.editar');

        Storage::disk('public')->delete(array_filter([$carrossel->imagem_desktop, $carrossel->imagem_mobile]));

        $carrossel->delete();

        RegistradorDeAuditoria::registrar('excluir', 'cms', 'CarrosselItem', $carrossel->id);

        return redirect()
            ->route('admin.carrossel.index')
            ->with('sucesso', 'Item do carrossel removido com sucesso.');
    }
}
