<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\GrauMaconico;
use App\Enums\SituacaoCadastralIrmao;
use Database\Factories\IrmaoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'nome_completo',
    'nome_social',
    'data_nascimento',
    'cpf',
    'rg',
    'email',
    'telefone',
    'endereco',
    'numero',
    'complemento',
    'bairro',
    'cidade',
    'estado',
    'cep',
    'data_iniciacao',
    'data_elevacao',
    'data_exaltacao',
    'cim',
    'grau_atual',
    'situacao_cadastral',
    'cargo_atual',
    'data_ingresso_loja',
    'data_desligamento',
    'observacoes_administrativas',
    'fotografia',
])]
#[Hidden(['cpf', 'rg', 'observacoes_administrativas'])]
final class Irmao extends Model
{
    /** @use HasFactory<IrmaoFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'data_nascimento' => 'date',
            'data_iniciacao' => 'date',
            'data_elevacao' => 'date',
            'data_exaltacao' => 'date',
            'data_ingresso_loja' => 'date',
            'data_desligamento' => 'date',
            'grau_atual' => GrauMaconico::class,
            'situacao_cadastral' => SituacaoCadastralIrmao::class,
        ];
    }

    public function usuario(): HasOne
    {
        return $this->hasOne(User::class, 'irmao_id');
    }

    public function historicos(): HasMany
    {
        return $this->hasMany(IrmaoHistorico::class)->latest('data_referencia');
    }
}
