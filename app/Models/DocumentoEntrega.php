<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\StatusEntregaDocumentoTrabalho;
use Database\Factories\DocumentoEntregaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['atividade_id', 'usuario_id', 'titulo', 'descricao', 'status', 'enviado_em'])]
final class DocumentoEntrega extends Model
{
    /** @use HasFactory<DocumentoEntregaFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $table = 'documento_entregas';

    protected function casts(): array
    {
        return [
            'status' => StatusEntregaDocumentoTrabalho::class,
            'enviado_em' => 'datetime',
        ];
    }

    public function atividade(): BelongsTo
    {
        return $this->belongsTo(DocumentoAtividade::class, 'atividade_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function avaliacao(): HasOne
    {
        return $this->hasOne(DocumentoAvaliacao::class, 'entrega_id');
    }

    public function comentarios(): HasMany
    {
        return $this->hasMany(DocumentoComentario::class, 'entrega_id');
    }

    public function arquivos(): HasMany
    {
        return $this->hasMany(DocumentoArquivo::class, 'entrega_id');
    }
}
