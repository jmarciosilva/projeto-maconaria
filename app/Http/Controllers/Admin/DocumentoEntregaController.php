<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\StatusDocumentoTrabalho;
use App\Enums\StatusEntregaDocumentoTrabalho;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AvaliarDocumentoEntregaRequest;
use App\Http\Requests\Admin\SalvarDocumentoComentarioRequest;
use App\Http\Requests\Admin\SalvarDocumentoEntregaRequest;
use App\Models\DocumentoAtividade;
use App\Models\DocumentoEntrega;
use App\Support\RegistradorDeAuditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

final class DocumentoEntregaController extends Controller
{
    public function store(SalvarDocumentoEntregaRequest $request, DocumentoAtividade $atividade): RedirectResponse
    {
        abort_unless($atividade->status === StatusDocumentoTrabalho::PUBLICADA, 422, 'Somente atividades publicadas aceitam entregas.');

        DB::transaction(function () use ($request, $atividade): void {
            $dados = $request->validated();
            unset($dados['arquivos']);

            $entrega = $atividade->entregas()->create([
                ...$dados,
                'usuario_id' => $request->user()->id,
                'status' => StatusEntregaDocumentoTrabalho::ENVIADA,
                'enviado_em' => now(),
            ]);

            foreach ($request->file('arquivos', []) as $arquivo) {
                $caminho = $arquivo->store("documentos/entregas/{$entrega->id}", 'local');

                $entrega->arquivos()->create([
                    'atividade_id' => $atividade->id,
                    'enviado_por_id' => $request->user()->id,
                    'nome_original' => $arquivo->getClientOriginalName(),
                    'caminho' => $caminho,
                    'mime' => $arquivo->getClientMimeType() ?: 'application/octet-stream',
                    'tamanho' => $arquivo->getSize(),
                ]);
            }

            RegistradorDeAuditoria::registrar('entregar', 'documentos', 'DocumentoEntrega', $entrega->id);
        });

        return back()->with('sucesso', 'Entrega enviada com sucesso.');
    }

    public function avaliar(AvaliarDocumentoEntregaRequest $request, DocumentoEntrega $entrega): RedirectResponse
    {
        DB::transaction(function () use ($request, $entrega): void {
            $entrega->avaliacao()->updateOrCreate(
                ['entrega_id' => $entrega->id],
                [
                    ...$request->validated(),
                    'avaliador_id' => $request->user()->id,
                    'avaliado_em' => now(),
                ],
            );

            $entrega->update(['status' => StatusEntregaDocumentoTrabalho::AVALIADA]);

            RegistradorDeAuditoria::registrar('avaliar', 'documentos', 'DocumentoEntrega', $entrega->id);
        });

        return back()->with('sucesso', 'Entrega avaliada com sucesso.');
    }

    public function comentar(SalvarDocumentoComentarioRequest $request, DocumentoAtividade $atividade): RedirectResponse
    {
        $dados = $request->validated();

        if (isset($dados['entrega_id'])) {
            abort_unless(
                DocumentoEntrega::query()->where('atividade_id', $atividade->id)->whereKey($dados['entrega_id'])->exists(),
                404,
            );
        }

        DB::transaction(function () use ($request, $atividade, $dados): void {
            $atividade->comentarios()->create([
                ...$dados,
                'usuario_id' => $request->user()->id,
            ]);

            RegistradorDeAuditoria::registrar('comentar', 'documentos', 'DocumentoAtividade', $atividade->id);
        });

        return back()->with('sucesso', 'Comentário registrado com sucesso.');
    }
}
