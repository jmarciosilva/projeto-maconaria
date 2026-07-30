<?php

namespace Database\Factories;

use App\Models\NoticiaTag;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<NoticiaTag>
 */
class NoticiaTagFactory extends Factory
{
    public function definition(): array
    {
        $nome = fake()->unique()->word();

        return [
            'nome' => ucfirst($nome),
            'slug' => Str::slug($nome),
        ];
    }
}
