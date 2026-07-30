<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\StatusDocumentoTrabalho;
use Database\Factories\DocumentoAtividadeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['autor_id', 'titulo', 'descricao', 'status', 'publicado_em', 'prazo_entrega_em'])]
final class DocumentoAtividade extends Model
{
    /** @use HasFactory<DocumentoAtividadeFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $table = 'documento_atividades';

    protected function casts(): array
    {
        return [
            'status' => StatusDocumentoTrabalho::class,
            'publicado_em' => 'datetime',
            'prazo_entrega_em' => 'datetime',
        ];
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autor_id');
    }

    public function entregas(): HasMany
    {
        return $this->hasMany(DocumentoEntrega::class, 'atividade_id');
    }

    public function comentarios(): HasMany
    {
        return $this->hasMany(DocumentoComentario::class, 'atividade_id');
    }

    public function arquivos(): HasMany
    {
        return $this->hasMany(DocumentoArquivo::class, 'atividade_id');
    }
}
