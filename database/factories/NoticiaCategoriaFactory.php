<?php

namespace Database\Factories;

use App\Models\NoticiaCategoria;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<NoticiaCategoria>
 */
class NoticiaCategoriaFactory extends Factory
{
    public function definition(): array
    {
        $nome = fake()->unique()->words(2, true);

        return [
            'nome' => ucfirst($nome),
            'slug' => Str::slug($nome),
            'descricao' => fake()->sentence(),
            'ativa' => true,
        ];
    }
}
