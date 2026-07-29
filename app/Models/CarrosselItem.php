<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CarrosselItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

#[Fillable([
    'titulo',
    'subtitulo',
    'imagem_desktop',
    'imagem_mobile',
    'texto_alternativo',
    'link',
    'texto_botao',
    'abrir_em_nova_aba',
    'ordem',
    'data_inicio',
    'data_fim',
    'ativo',
])]
final class CarrosselItem extends Model
{
    /** @use HasFactory<CarrosselItemFactory> */
    use HasFactory;

    protected $table = 'carrossel_itens';

    protected function casts(): array
    {
        return [
            'abrir_em_nova_aba' => 'boolean',
            'ativo' => 'boolean',
            'data_inicio' => 'date',
            'data_fim' => 'date',
        ];
    }

    public function scopeAtivo(Builder $query): Builder
    {
        return $query->where('ativo', true);
    }

    /**
     * Filtra apenas os itens dentro do período de exibição configurado
     * (data_inicio/data_fim nulas significam "sem limite" naquele lado).
     */
    public function scopeVigente(Builder $query): Builder
    {
        $hoje = Carbon::today()->toDateString();

        return $query
            ->where(fn ($q) => $q->whereNull('data_inicio')->orWhere('data_inicio', '<=', $hoje))
            ->where(fn ($q) => $q->whereNull('data_fim')->orWhere('data_fim', '>=', $hoje));
    }

    public function scopeOrdenado(Builder $query): Builder
    {
        return $query->orderBy('ordem')->orderBy('id');
    }
}
