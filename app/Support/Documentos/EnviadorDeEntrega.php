<?php

declare(strict_types=1);

namespace App\Support\Documentos;

use App\Enums\StatusDocumentoTrabalho;
use App\Enums\StatusEntregaDocumentoTrabalho;
use App\Models\DocumentoAtividade;
use App\Models\DocumentoEntrega;
use App\Models\User;
use App\Support\RegistradorDeAuditoria;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Envio de entrega é usado tanto pelo painel (Admin\DocumentoEntregaController)
 * quanto pela API mobile (Fase 12) — centralizado aqui para as duas nunca
 * divergirem na regra de armazenamento (disco privado, sem URL pública).
 */
final class EnviadorDeEntrega
{
    /**
     * @param  array<int, UploadedFile>  $arquivos
     */
    public function enviar(DocumentoAtividade $atividade, User $usuario, string $titulo, ?string $descricao, array $arquivos): DocumentoEntrega
    {
        abort_unless($atividade->status === StatusDocumentoTrabalho::PUBLICADA, 422, 'Somente atividades publicadas aceitam entregas.');

        return DB::transaction(function () use ($atividade, $usuario, $titulo, $descricao, $arquivos): DocumentoEntrega {
            $entrega = $atividade->entregas()->create([
                'titulo' => $titulo,
                'descricao' => $descricao,
                'usuario_id' => $usuario->id,
                'status' => StatusEntregaDocumentoTrabalho::ENVIADA,
                'enviado_em' => now(),
            ]);

            foreach ($arquivos as $arquivo) {
                $caminho = $arquivo->store("documentos/entregas/{$entrega->id}", 'local');

                $entrega->arquivos()->create([
                    'atividade_id' => $atividade->id,
                    'enviado_por_id' => $usuario->id,
                    'nome_original' => $arquivo->getClientOriginalName(),
                    'caminho' => $caminho,
                    'mime' => $arquivo->getClientMimeType() ?: 'application/octet-stream',
                    'tamanho' => $arquivo->getSize(),
                ]);
            }

            RegistradorDeAuditoria::registrar('entregar', 'documentos', 'DocumentoEntrega', $entrega->id);

            return $entrega;
        });
    }
}
