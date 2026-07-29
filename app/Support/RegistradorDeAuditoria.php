<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Auditoria;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Centraliza o registro de auditoria para não espalhar a lógica de criação
 * do log em cada controller. Nunca deve receber senhas, tokens ou segredos.
 */
final class RegistradorDeAuditoria
{
    public static function registrar(
        string $acao,
        string $modulo,
        ?string $entidade = null,
        ?int $entidadeId = null,
        ?array $dadosAnteriores = null,
        ?array $dadosNovos = null,
    ): Auditoria {
        return Auditoria::create([
            'usuario_id' => Auth::id(),
            'acao' => $acao,
            'modulo' => $modulo,
            'entidade' => $entidade,
            'entidade_id' => $entidadeId,
            'dados_anteriores' => $dadosAnteriores,
            'dados_novos' => $dadosNovos,
            'endereco_ip' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
