<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TipoLancamentoFinanceiro;
use Database\Factories\TesourariaCategoriaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['nome', 'tipo', 'ativa'])]
final class TesourariaCategoria extends Model
{
    /** @use HasFactory<TesourariaCategoriaFactory> */
    use HasFactory;

    protected $table = 'tesouraria_categorias';

    protected function casts(): array
    {
        return [
            'tipo' => TipoLancamentoFinanceiro::class,
            'ativa' => 'boolean',
        ];
    }
}
