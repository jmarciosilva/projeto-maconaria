<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\StatusDocumentoTrabalho;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SalvarDocumentoAtividadeRequest;
use App\Models\DocumentoArquivo;
use App\Models\DocumentoAtividade;
use App\Support\RegistradorDeAuditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class DocumentoAtividadeController extends Controller
{
    public function index(): View
    {
        $this->authorize('documentos.visualizar');

        $atividades = DocumentoAtividade::query()
            ->withCount('entregas')
            ->latest('created_at')
            ->paginate(20);

        return view('admin.documentos.atividades.index', compact('atividades'));
    }

    public function create(): View
    {
        $this->authorize('documentos.avaliar');

        return view('admin.documentos.atividades.create', $this->dadosFormulario());
    }

    public function store(SalvarDocumentoAtividadeRequest $request): RedirectResponse
    {
        $atividade = DB::transaction(function () use ($request): DocumentoAtividade {
            $dados = $request->validated();
            unset($dados['arquivos']);

            $atividade = DocumentoAtividade::create([
                ...$dados,
                'autor_id' => $request->user()->id,
                'publicado_em' => $dados['status'] === StatusDocumentoTrabalho::PUBLICADA->value ? now() : null,
            ]);

            $this->armazenarArquivosDaAtividade($atividade, $request);
            RegistradorDeAuditoria::registrar('criar', 'documentos', 'DocumentoAtividade', $atividade->id);

            return $atividade;
        });

        return redirect()->route('admin.documentos.atividades.show', $atividade)->with('sucesso', 'Atividade cadastrada com sucesso.');
    }

    public function show(DocumentoAtividade $atividade): View
    {
        $this->authorize('documentos.visualizar');

        $atividade->load([
            'autor',
            'arquivos.enviadoPor',
            'comentarios.usuario',
            'entregas.usuario',
            'entregas.avaliacao.avaliador',
            'entregas.arquivos',
            'entregas.comentarios.usuario',
        ]);

        return view('admin.documentos.atividades.show', compact('atividade'));
    }

    public function edit(DocumentoAtividade $atividade): View
    {
        $this->authorize('documentos.avaliar');

        return view('admin.documentos.atividades.edit', [
            ...$this->dadosFormulario(),
            'atividade' => $atividade,
        ]);
    }

    public function update(SalvarDocumentoAtividadeRequest $request, DocumentoAtividade $atividade): RedirectResponse
    {
        DB::transaction(function () use ($request, $atividade): void {
            $dadosAnteriores = $atividade->only(['titulo', 'status']);
            $dados = $request->validated();
            unset($dados['arquivos']);

            $atividade->fill([
                ...$dados,
                'publicado_em' => $dados['status'] === StatusDocumentoTrabalho::PUBLICADA->value ? ($atividade->publicado_em ?? now()) : null,
            ])->save();

            $this->armazenarArquivosDaAtividade($atividade, $request);

            RegistradorDeAuditoria::registrar(
                acao: 'editar',
                modulo: 'documentos',
                entidade: 'DocumentoAtividade',
                entidadeId: $atividade->id,
                dadosAnteriores: $dadosAnteriores,
                dadosNovos: $atividade->only(['titulo', 'status']),
            );
        });

        return redirect()->route('admin.documentos.atividades.show', $atividade)->with('sucesso', 'Atividade atualizada com sucesso.');
    }

    public function destroy(DocumentoAtividade $atividade): RedirectResponse
    {
        $this->authorize('documentos.excluir');

        DB::transaction(function () use ($atividade): void {
            $atividade->delete();
            RegistradorDeAuditoria::registrar('excluir', 'documentos', 'DocumentoAtividade', $atividade->id);
        });

        return redirect()->route('admin.documentos.atividades.index')->with('sucesso', 'Atividade removida com sucesso.');
    }

    public function baixarArquivo(DocumentoArquivo $arquivo): StreamedResponse
    {
        $this->authorize('download', $arquivo);

        return Storage::disk('local')->download($arquivo->caminho, $arquivo->nome_original);
    }

    private function dadosFormulario(): array
    {
        return [
            'statusDisponiveis' => collect(StatusDocumentoTrabalho::cases())->mapWithKeys(fn (StatusDocumentoTrabalho $status) => [$status->value => $status->rotulo()]),
        ];
    }

    private function armazenarArquivosDaAtividade(DocumentoAtividade $atividade, SalvarDocumentoAtividadeRequest $request): void
    {
        foreach ($request->file('arquivos', []) as $arquivo) {
            $caminho = $arquivo->store("documentos/atividades/{$atividade->id}", 'local');

            $atividade->arquivos()->create([
                'enviado_por_id' => $request->user()->id,
                'nome_original' => $arquivo->getClientOriginalName(),
                'caminho' => $caminho,
                'mime' => $arquivo->getClientMimeType() ?: 'application/octet-stream',
                'tamanho' => $arquivo->getSize(),
            ]);
        }
    }
}
