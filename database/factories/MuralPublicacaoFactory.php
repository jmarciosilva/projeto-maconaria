<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\StatusMuralGaleria;
use App\Enums\VisibilidadeMuralGaleria;
use App\Models\MuralPublicacao;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MuralPublicacao>
 */
final class MuralPublicacaoFactory extends Factory
{
    protected $model = MuralPublicacao::class;

    public function definition(): array
    {
        return [
            'autor_id' => User::factory(),
            'titulo' => fake()->sentence(4),
            'conteudo' => fake()->paragraphs(2, true),
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
