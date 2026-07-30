<?php

namespace Database\Factories;

use App\Models\ChancelariaVisitante;
use App\Models\Evento;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChancelariaVisitante>
 */
class ChancelariaVisitanteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'evento_id' => Evento::factory(),
            'registrado_por_id' => User::factory(),
            'nome' => fake()->name(),
            'loja_origem' => 'Loja '.fake()->word(),
            'potencia' => 'GOB',
            'documento' => fake()->numerify('###.###.###-##'),
            'observacao' => fake()->sentence(),
        ];
    }
}
