<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'documento_id',
    'enviado_por_id',
    'nome_original',
    'caminho',
    'mime',
    'tamanho',
])]
final class SecretariaDocumentoArquivo extends Model
{
    protected $table = 'secretaria_documento_arquivos';

    protected function casts(): array
    {
        return [
            'tamanho' => 'integer',
        ];
    }

    public function documento(): BelongsTo
    {
        return $this->belongsTo(SecretariaDocumento::class, 'documento_id');
    }

    public function enviadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enviado_por_id');
    }
}
