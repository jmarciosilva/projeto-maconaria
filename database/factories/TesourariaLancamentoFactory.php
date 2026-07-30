<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\StatusLancamentoFinanceiro;
use App\Enums\TipoLancamentoFinanceiro;
use App\Models\TesourariaCategoria;
use App\Models\TesourariaConta;
use App\Models\TesourariaLancamento;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TesourariaLancamento>
 */
final class TesourariaLancamentoFactory extends Factory
{
    protected $model = TesourariaLancamento::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tipo = fake()->randomElement(TipoLancamentoFinanceiro::cases());

        return [
            'categoria_id' => TesourariaCategoria::factory()->state(['tipo' => $tipo]),
            'conta_id' => TesourariaConta::factory(),
            'criado_por_id' => User::factory(),
            'tipo' => $tipo,
            'status' => StatusLancamentoFinanceiro::PENDENTE,
            'descricao' => fake()->sentence(4),
            'valor_centavos' => fake()->numberBetween(1000, 200000),
            'data_competencia' => now()->toDateString(),
            'data_vencimento' => now()->addDays(10)->toDateString(),
        ];
    }

    public function receita(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo' => TipoLancamentoFinanceiro::RECEITA,
            'categoria_id' => TesourariaCategoria::factory()->receita(),
        ]);
    }

    public function despesa(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo' => TipoLancamentoFinanceiro::DESPESA,
            'categoria_id' => TesourariaCategoria::factory()->despesa(),
        ]);
    }

    public function aprovado(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StatusLancamentoFinanceiro::APROVADO,
            'aprovado_em' => now(),
        ]);
    }

    public function baixado(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StatusLancamentoFinanceiro::BAIXADO,
            'data_pagamento' => now()->toDateString(),
        ]);
    }
}
