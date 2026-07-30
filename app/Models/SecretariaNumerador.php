<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TipoDocumentoSecretaria;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'tipo',
    'ano',
    'proximo_numero',
])]
final class SecretariaNumerador extends Model
{
    protected $table = 'secretaria_numeradores';

    protected function casts(): array
    {
        return [
            'tipo' => TipoDocumentoSecretaria::class,
            'ano' => 'integer',
            'proximo_numero' => 'integer',
        ];
    }
}
