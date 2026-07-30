<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\StatusMuralGaleria;
use App\Enums\VisibilidadeMuralGaleria;
use App\Models\GaleriaAlbum;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<GaleriaAlbum>
 */
final class GaleriaAlbumFactory extends Factory
{
    protected $model = GaleriaAlbum::class;

    public function definition(): array
    {
        $titulo = fake()->unique()->sentence(3);

        return [
            'autor_id' => User::factory(),
            'titulo' => $titulo,
            'slug' => Str::slug($titulo),
            'descricao' => fake()->paragraph(),
            'status' => StatusMuralGaleria::RASCUNHO,
            'visibilidade' => VisibilidadeMuralGaleria::RESTRITA,
        ];
    }

    public function publicado(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StatusMuralGaleria::PUBLICADO,
            'publicado_em' => now(),
        ]);
    }

    public function publico(): static
    {
        return $this->state(fn (array $attributes) => [
            'visibilidade' => VisibilidadeMuralGaleria::PUBLICA,
        ]);
    }
}
