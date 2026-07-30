<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\StatusDocumentoSecretaria;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'documento_id',
    'usuario_id',
    'versao',
    'titulo',
    'conteudo',
    'status',
])]
final class SecretariaDocumentoVersao extends Model
{
    protected $table = 'secretaria_documento_versoes';

    protected function casts(): array
    {
        return [
            'status' => StatusDocumentoSecretaria::class,
            'versao' => 'integer',
        ];
    }

    public function documento(): BelongsTo
    {
        return $this->belongsTo(SecretariaDocumento::class, 'documento_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
