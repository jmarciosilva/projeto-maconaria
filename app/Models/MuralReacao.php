<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TipoReacaoMural;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['publicacao_id', 'usuario_id', 'tipo'])]
final class MuralReacao extends Model
{
    protected $table = 'mural_reacoes';

    protected function casts(): array
    {
        return [
            'tipo' => TipoReacaoMural::class,
        ];
    }

    public function publicacao(): BelongsTo
    {
        return $this->belongsTo(MuralPublicacao::class, 'publicacao_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
