<?php

namespace Database\Factories;

use App\Enums\StatusComunicadoChancelaria;
use App\Models\ChancelariaComunicado;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChancelariaComunicado>
 */
class ChancelariaComunicadoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'autor_id' => User::factory(),
            'titulo' => fake()->sentence(4),
            'conteudo' => '<p>'.fake()->paragraph().'</p>',
            'status' => StatusComunicadoChancelaria::RASCUNHO,
            'publicado_em' => null,
        ];
    }
}
