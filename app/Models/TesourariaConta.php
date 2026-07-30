<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TipoContaFinanceira;
use Database\Factories\TesourariaContaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['nome', 'instituicao', 'tipo', 'saldo_inicial_centavos', 'ativa'])]
final class TesourariaConta extends Model
{
    /** @use HasFactory<TesourariaContaFactory> */
    use HasFactory;

    protected $table = 'tesouraria_contas';

    protected function casts(): array
    {
        return [
            'tipo' => TipoContaFinanceira::class,
            'saldo_inicial_centavos' => 'integer',
            'ativa' => 'boolean',
        ];
    }
}
