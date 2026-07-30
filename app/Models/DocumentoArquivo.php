<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['atividade_id', 'entrega_id', 'enviado_por_id', 'nome_original', 'caminho', 'mime', 'tamanho'])]
final class DocumentoArquivo extends Model
{
    protected $table = 'documento_arquivos';

    protected function casts(): array
    {
        return [
            'tamanho' => 'integer',
        ];
    }

    public function atividade(): BelongsTo
    {
        return $this->belongsTo(DocumentoAtividade::class, 'atividade_id');
    }

    public function entrega(): BelongsTo
    {
        return $this->belongsTo(DocumentoEntrega::class, 'entrega_id');
    }

    public function enviadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enviado_por_id');
    }
}
