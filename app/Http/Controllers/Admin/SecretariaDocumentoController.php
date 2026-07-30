<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\StatusDocumentoSecretaria;
use App\Enums\TipoDocumentoSecretaria;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SalvarSecretariaDocumentoRequest;
use App\Models\SecretariaDocumento;
use App\Models\SecretariaDocumentoArquivo;
use App\Support\RegistradorDeAuditoria;
use App\Support\Secretaria\ProcessadorConteudoSecretaria;
use App\Support\Secretaria\ProximoNumeroDocumento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class SecretariaDocumentoController extends Controller
{
    public function index(): View
    {
        $this->authorize('secretaria.visualizar');

        $documentos = SecretariaDocumento::query()
            ->with('autor')
            ->latest('created_at')
            ->paginate(20);

        return view('admin.secretaria.documentos.index', compact('documentos'));
    }

    public function create(): View
    {
        $this->authorize('secretaria.criar-ata');

        return view('admin.secretaria.documentos.create', $this->dadosFormulario());
    }

    public function store(SalvarSecretariaDocumentoRequest $request, ProximoNumeroDocumento $numerador): RedirectResponse
    {
        $documento = DB::transaction(function () use ($request, $numerador): SecretariaDocumento {
            $dados = $request->validated();
            $tipo = TipoDocumentoSecretaria::from($dados['tipo']);
            $ano = (int) ($request->date('data_documento')?->year ?? now()->year);
            $numero = $numerador->reservar($tipo, $ano);
            $dados['conteudo'] = ProcessadorConteudoSecretaria::prepararParaSalvar($request->input('conteudo'));
            unset($dados['arquivos']);

            $documento = SecretariaDocumento::create([
                ...$dados,
                'autor_id' => $request->user()->id,
                'tipo' => $tipo,
                'ano' => $ano,
                'numero' => $numero,
                'codigo' => $numerador->codigo($tipo, $ano, $numero),
            ]);

            $this->armazenarArquivos($documento, $request);
            $this->registrarVersao($documento);
            RegistradorDeAuditoria::registrar('criar', 'secretaria', 'SecretariaDocumento', $documento->id);

            return $documento;
        });

        return redirect()
            ->route('admin.secretaria.documentos.edit', $documento)
            ->with('sucesso', 'Documento da Secretaria cadastrado com sucesso.');
    }

    public function edit(SecretariaDocumento $documento): View
    {
        $this->authorize('secretaria.editar-ata');

        $documento->load(['versoes.usuario', 'arquivos.enviadoPor']);

        return view('admin.secretaria.documentos.edit', [
            ...$this->dadosFormulario(),
            'documento' => $documento,
        ]);
    }

    public function update(SalvarSecretariaDocumentoRequest $request, SecretariaDocumento $documento): RedirectResponse
    {
        abort_if(
            in_array($documento->status, [StatusDocumentoSecretaria::APROVADO, StatusDocumentoSecretaria::PUBLICADO], true),
            422,
            'Documentos aprovados ou publicados não podem ser editados diretamente.'
        );

        DB::transaction(function () use ($request, $documento): void {
            $dadosAnteriores = $documento->only(['titulo', 'status']);
            $dados = $request->validated();
            $dados['conteudo'] = ProcessadorConteudoSecretaria::prepararParaSalvar($request->input('conteudo'));
            unset($dados['arquivos']);

            $documento->fill($dados)->save();

            $this->armazenarArquivos($documento, $request);
            $this->registrarVersao($documento);

            RegistradorDeAuditoria::registrar(
                acao: 'editar',
                modulo: 'secretaria',
                entidade: 'SecretariaDocumento',
                entidadeId: $documento->id,
                dadosAnteriores: $dadosAnteriores,
                dadosNovos: $documento->only(['titulo', 'status']),
            );
        });

        return redirect()
            ->route('admin.secretaria.documentos.edit', $documento)
            ->with('sucesso', 'Documento da Secretaria atualizado com sucesso.');
    }

    public function aprovar(SecretariaDocumento $documento): RedirectResponse
    {
        $this->authorize('secretaria.aprovar-ata');
        abort_unless($documento->podeSerAprovado(), 422, 'Este documento não pode ser aprovado no status atual.');

        DB::transaction(function () use ($documento): void {
            $documento->update([
                'status' => StatusDocumentoSecretaria::APROVADO,
                'aprovado_por_id' => auth()->id(),
                'aprovado_em' => now(),
            ]);

            RegistradorDeAuditoria::registrar('aprovar', 'secretaria', 'SecretariaDocumento', $documento->id);
        });

        return back()->with('sucesso', 'Documento aprovado com sucesso.');
    }

    public function publicar(SecretariaDocumento $documento): RedirectResponse
    {
        $this->authorize('secretaria.publicar-ata');
        abort_unless($documento->podeSerPublicado(), 422, 'Somente documentos aprovados podem ser publicados.');

        DB::transaction(function () use ($documento): void {
            $documento->update([
                'status' => StatusDocumentoSecretaria::PUBLICADO,
                'publicado_por_id' => auth()->id(),
                'publicado_em' => now(),
            ]);

            RegistradorDeAuditoria::registrar('publicar', 'secretaria', 'SecretariaDocumento', $documento->id);
        });

        return back()->with('sucesso', 'Documento publicado com sucesso.');
    }

    public function destroy(SecretariaDocumento $documento): RedirectResponse
    {
        $this->authorize('secretaria.editar-ata');

        DB::transaction(function () use ($documento): void {
            $documento->delete();

            RegistradorDeAuditoria::registrar('excluir', 'secretaria', 'SecretariaDocumento', $documento->id);
        });

        return redirect()
            ->route('admin.secretaria.documentos.index')
            ->with('sucesso', 'Documento removido com sucesso.');
    }

    public function baixarArquivo(SecretariaDocumento $documento, SecretariaDocumentoArquivo $arquivo): StreamedResponse
    {
        $this->authorize('secretaria.visualizar');
        abort_unless($arquivo->documento_id === $documento->id, 404);

        return Storage::disk('local')->download($arquivo->caminho, $arquivo->nome_original);
    }

    public function removerArquivo(SecretariaDocumento $documento, SecretariaDocumentoArquivo $arquivo): RedirectResponse
    {
        $this->authorize('secretaria.editar-ata');
        abort_unless($arquivo->documento_id === $documento->id, 404);

        DB::transaction(function () use ($arquivo): void {
            Storage::disk('local')->delete($arquivo->caminho);
            $arquivo->delete();

            RegistradorDeAuditoria::registrar('excluir-arquivo', 'secretaria', 'SecretariaDocumentoArquivo', $arquivo->id);
        });

        return back()->with('sucesso', 'Arquivo removido com sucesso.');
    }

    private function dadosFormulario(): array
    {
        return [
            'tipos' => collect(TipoDocumentoSecretaria::cases())->mapWithKeys(fn (TipoDocumentoSecretaria $tipo) => [$tipo->value => $tipo->rotulo()]),
            'statusDisponiveis' => [
                StatusDocumentoSecretaria::RASCUNHO->value => StatusDocumentoSecretaria::RASCUNHO->rotulo(),
                StatusDocumentoSecretaria::EM_APROVACAO->value => StatusDocumentoSecretaria::EM_APROVACAO->rotulo(),
            ],
        ];
    }

    private function registrarVersao(SecretariaDocumento $documento): void
    {
        $documento->versoes()->create([
            'usuario_id' => auth()->id(),
            'versao' => ((int) $documento->versoes()->max('versao')) + 1,
            'titulo' => $documento->titulo,
            'conteudo' => $documento->conteudo,
            'status' => $documento->status,
        ]);
    }

    private function armazenarArquivos(SecretariaDocumento $documento, SalvarSecretariaDocumentoRequest $request): void
    {
        foreach ($request->file('arquivos', []) as $arquivo) {
            $caminho = $arquivo->store("secretaria/documentos/{$documento->id}", 'local');

            $documento->arquivos()->create([
                'enviado_por_id' => $request->user()->id,
                'nome_original' => $arquivo->getClientOriginalName(),
                'caminho' => $caminho,
                'mime' => $arquivo->getClientMimeType() ?: 'application/octet-stream',
                'tamanho' => $arquivo->getSize(),
            ]);
        }
    }
}
