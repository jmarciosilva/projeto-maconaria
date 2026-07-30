<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TipoContaFinanceira;
use App\Models\TesourariaConta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TesourariaConta>
 */
final class TesourariaContaFactory extends Factory
{
    protected $model = TesourariaConta::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nome' => fake()->unique()->words(2, true),
            'instituicao' => fake()->optional()->company(),
            'tipo' => fake()->randomElement(TipoContaFinanceira::cases()),
            'saldo_inicial_centavos' => fake()->numberBetween(0, 100000),
            'ativa' => true,
        ];
    }
}
