<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CarrosselItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CarrosselItem>
 */
final class CarrosselItemFactory extends Factory
{
    protected $model = CarrosselItem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'titulo' => $this->faker->sentence(3),
            'imagem_desktop' => 'carrossel/exemplo-desktop.jpg',
            'texto_alternativo' => $this->faker->sentence(4),
            'ordem' => 0,
            'ativo' => true,
        ];
    }
}
