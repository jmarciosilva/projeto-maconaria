<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\StatusMuralGaleria;
use App\Enums\VisibilidadeMuralGaleria;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SalvarGaleriaAlbumRequest;
use App\Models\GaleriaAlbum;
use App\Support\RegistradorDeAuditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class GaleriaAlbumController extends Controller
{
    public function index(): View
    {
        $this->authorize('galeria.visualizar');

        $albuns = GaleriaAlbum::query()->withCount('fotografias')->latest('created_at')->paginate(20);

        return view('admin.galeria.albuns.index', compact('albuns'));
    }

    public function create(): View
    {
        $this->authorize('galeria.criar');

        return view('admin.galeria.albuns.create', $this->dadosFormulario());
    }

    public function store(SalvarGaleriaAlbumRequest $request): RedirectResponse
    {
        $album = DB::transaction(function () use ($request): GaleriaAlbum {
            $dados = $this->dadosPersistencia($request);
            $album = GaleriaAlbum::create([...$dados, 'autor_id' => $request->user()->id]);

            $this->armazenarFotografias($album, $request);
            RegistradorDeAuditoria::registrar('criar', 'galeria', 'GaleriaAlbum', $album->id);

            return $album;
        });

        return redirect()->route('admin.galeria.albuns.show', $album)->with('sucesso', 'Álbum cadastrado com sucesso.');
    }

    public function show(GaleriaAlbum $album): View
    {
        $this->authorize('galeria.visualizar');

        $album->load(['fotografias', 'autor']);

        return view('admin.galeria.albuns.show', compact('album'));
    }

    public function edit(GaleriaAlbum $album): View
    {
        $this->authorize('galeria.editar');

        return view('admin.galeria.albuns.edit', [...$this->dadosFormulario(), 'album' => $album]);
    }

    public function update(SalvarGaleriaAlbumRequest $request, GaleriaAlbum $album): RedirectResponse
    {
        DB::transaction(function () use ($request, $album): void {
            $dadosAnteriores = $album->only(['titulo', 'status', 'visibilidade']);
            $album->fill($this->dadosPersistencia($request, $album))->save();
            $this->armazenarFotografias($album, $request);

            RegistradorDeAuditoria::registrar(
                acao: 'editar',
                modulo: 'galeria',
                entidade: 'GaleriaAlbum',
                entidadeId: $album->id,
                dadosAnteriores: $dadosAnteriores,
                dadosNovos: $album->only(['titulo', 'status', 'visibilidade']),
            );
        });

        return redirect()->route('admin.galeria.albuns.show', $album)->with('sucesso', 'Álbum atualizado com sucesso.');
    }

    public function destroy(GaleriaAlbum $album): RedirectResponse
    {
        $this->authorize('galeria.excluir');

        DB::transaction(function () use ($album): void {
            foreach ($album->fotografias as $fotografia) {
                Storage::disk('public')->delete($fotografia->caminho);
            }

            $album->delete();
            RegistradorDeAuditoria::registrar('excluir', 'galeria', 'GaleriaAlbum', $album->id);
        });

        return redirect()->route('admin.galeria.albuns.index')->with('sucesso', 'Álbum removido com sucesso.');
    }

    private function dadosFormulario(): array
    {
        return [
            'statusDisponiveis' => collect(StatusMuralGaleria::cases())->mapWithKeys(fn (StatusMuralGaleria $status) => [$status->value => $status->rotulo()]),
            'visibilidades' => collect(VisibilidadeMuralGaleria::cases())->mapWithKeys(fn (VisibilidadeMuralGaleria $visibilidade) => [$visibilidade->value => $visibilidade->rotulo()]),
        ];
    }

    private function dadosPersistencia(SalvarGaleriaAlbumRequest $request, ?GaleriaAlbum $album = null): array
    {
        $dados = $request->safe()->except(['fotografias', 'textos_alternativos']);
        $dados['slug'] = ($dados['slug'] ?? null) ?: Str::slug($dados['titulo']);
        $dados['publicado_em'] = $dados['status'] === StatusMuralGaleria::PUBLICADO->value ? ($album?->publicado_em ?? now()) : null;

        return $dados;
    }

    private function armazenarFotografias(GaleriaAlbum $album, SalvarGaleriaAlbumRequest $request): void
    {
        foreach ($request->file('fotografias', []) as $indice => $arquivo) {
            $album->fotografias()->create([
                'enviado_por_id' => $request->user()->id,
                'texto_alternativo' => $request->input("textos_alternativos.{$indice}") ?: $album->titulo,
                'caminho' => $arquivo->store("galeria/albuns/{$album->id}", 'public'),
                'mime' => $arquivo->getClientMimeType() ?: 'application/octet-stream',
                'tamanho' => $arquivo->getSize(),
                'ordem' => $album->fotografias()->count() + 1,
            ]);
        }
    }
}
