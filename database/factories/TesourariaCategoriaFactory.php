<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TipoLancamentoFinanceiro;
use App\Models\TesourariaCategoria;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TesourariaCategoria>
 */
final class TesourariaCategoriaFactory extends Factory
{
    protected $model = TesourariaCategoria::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nome' => fake()->unique()->words(2, true),
            'tipo' => fake()->randomElement(TipoLancamentoFinanceiro::cases()),
            'ativa' => true,
        ];
    }

    public function receita(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo' => TipoLancamentoFinanceiro::RECEITA,
        ]);
    }

    public function despesa(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo' => TipoLancamentoFinanceiro::DESPESA,
        ]);
    }
}
