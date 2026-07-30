<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\StatusDocumentoSecretaria;
use App\Enums\TipoDocumentoSecretaria;
use Database\Factories\SecretariaDocumentoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'autor_id',
    'aprovado_por_id',
    'publicado_por_id',
    'tipo',
    'ano',
    'numero',
    'codigo',
    'titulo',
    'conteudo',
    'status',
    'data_documento',
    'aprovado_em',
    'publicado_em',
])]
final class SecretariaDocumento extends Model
{
    /** @use HasFactory<SecretariaDocumentoFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $table = 'secretaria_documentos';

    protected function casts(): array
    {
        return [
            'tipo' => TipoDocumentoSecretaria::class,
            'status' => StatusDocumentoSecretaria::class,
            'ano' => 'integer',
            'numero' => 'integer',
            'data_documento' => 'date',
            'aprovado_em' => 'datetime',
            'publicado_em' => 'datetime',
        ];
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autor_id');
    }

    public function aprovadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprovado_por_id');
    }

    public function publicadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'publicado_por_id');
    }

    public function versoes(): HasMany
    {
        return $this->hasMany(SecretariaDocumentoVersao::class, 'documento_id');
    }

    public function arquivos(): HasMany
    {
        return $this->hasMany(SecretariaDocumentoArquivo::class, 'documento_id');
    }

    public function podeSerAprovado(): bool
    {
        return in_array($this->status, [StatusDocumentoSecretaria::RASCUNHO, StatusDocumentoSecretaria::EM_APROVACAO], true);
    }

    public function podeSerPublicado(): bool
    {
        return $this->status === StatusDocumentoSecretaria::APROVADO;
    }
}
