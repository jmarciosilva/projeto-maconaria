<?php

declare(strict_types=1);

namespace App\Support\Secretaria;

use App\Enums\TipoDocumentoSecretaria;
use App\Models\SecretariaNumerador;

final class ProximoNumeroDocumento
{
    public function reservar(TipoDocumentoSecretaria $tipo, int $ano): int
    {
        $numerador = SecretariaNumerador::query()
            ->where('tipo', $tipo->value)
            ->where('ano', $ano)
            ->lockForUpdate()
            ->first();

        if (! $numerador) {
            $numerador = SecretariaNumerador::create([
                'tipo' => $tipo,
                'ano' => $ano,
                'proximo_numero' => 1,
            ]);
        }

        $numero = $numerador->proximo_numero;

        // A reserva acontece dentro da mesma transação da criação do documento
        // para impedir dois documentos com o mesmo número em acessos simultâneos.
        $numerador->increment('proximo_numero');

        return $numero;
    }

    public function codigo(TipoDocumentoSecretaria $tipo, int $ano, int $numero): string
    {
        return sprintf('%s-%04d-%04d', $tipo->prefixo(), $ano, $numero);
    }
}
