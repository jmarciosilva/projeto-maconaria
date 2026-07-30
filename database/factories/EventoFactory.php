<?php

namespace Database\Factories;

use App\Enums\StatusEvento;
use App\Enums\TipoEvento;
use App\Enums\VisibilidadeEvento;
use App\Models\Evento;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Evento>
 */
class EventoFactory extends Factory
{
    public function definition(): array
    {
        $titulo = fake()->unique()->sentence(4);
        $inicio = now()->addDays(fake()->numberBetween(1, 30))->setTime(fake()->numberBetween(8, 20), 0);

        return [
            'autor_id' => User::factory(),
            'titulo' => $titulo,
            'slug' => Str::slug($titulo),
            'descricao' => fake()->paragraph(),
            'tipo' => TipoEvento::EVENTO,
            'status' => StatusEvento::RASCUNHO,
            'visibilidade' => VisibilidadeEvento::PUBLICA,
            'local' => fake()->city(),
            'inicio_em' => $inicio,
            'fim_em' => $inicio->copy()->addHours(2),
            'inscricoes_ate' => $inicio->copy()->subDay(),
            'capacidade' => null,
            'permite_confirmacao' => false,
        ];
    }

    public function publicado(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StatusEvento::PUBLICADO,
        ]);
    }

    public function restrito(): static
    {
        return $this->state(fn (array $attributes) => [
            'visibilidade' => VisibilidadeEvento::RESTRITA,
        ]);
    }

    public function comConfirmacao(): static
    {
        return $this->state(fn (array $attributes) => [
            'permite_confirmacao' => true,
            'inscricoes_ate' => now()->addDays(3),
        ]);
    }
}
