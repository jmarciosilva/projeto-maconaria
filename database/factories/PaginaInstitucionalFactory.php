<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PaginaInstitucional;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaginaInstitucional>
 */
final class PaginaInstitucionalFactory extends Factory
{
    protected $model = PaginaInstitucional::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slug' => $this->faker->unique()->slug(2),
            'titulo' => $this->faker->sentence(3),
            'conteudo' => '<p>'.$this->faker->paragraph().'</p>',
            'publicado' => true,
        ];
    }
}
