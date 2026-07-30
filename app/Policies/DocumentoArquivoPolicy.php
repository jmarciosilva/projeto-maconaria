<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\DocumentoArquivo;
use App\Models\User;

final class DocumentoArquivoPolicy
{
    public function download(User $user, DocumentoArquivo $arquivo): bool
    {
        if ($user->can('documentos.visualizar')) {
            return true;
        }

        return $arquivo->entrega?->usuario_id === $user->id;
    }
}
