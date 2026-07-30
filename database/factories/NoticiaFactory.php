<?php

namespace Database\Factories;

use App\Enums\StatusNoticia;
use App\Enums\VisibilidadeNoticia;
use App\Models\Noticia;
use App\Models\NoticiaCategoria;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Noticia>
 */
class NoticiaFactory extends Factory
{
    public function definition(): array
    {
        $titulo = fake()->unique()->sentence(4);

        return [
            'categoria_id' => NoticiaCategoria::factory(),
            'autor_id' => User::factory(),
            'titulo' => $titulo,
            'slug' => Str::slug($titulo),
            'resumo' => fake()->paragraph(),
            'conteudo' => '<p>'.fake()->paragraph().'</p>',
            'status' => StatusNoticia::RASCUNHO,
            'visibilidade' => VisibilidadeNoticia::PUBLICA,
            'destaque' => false,
            'publicado_em' => null,
            'agendado_para' => null,
        ];
    }

    public function publicada(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StatusNoticia::PUBLICADA,
            'publicado_em' => now()->subMinute(),
        ]);
    }

    public function restrita(): static
    {
        return $this->state(fn (array $attributes) => [
            'visibilidade' => VisibilidadeNoticia::RESTRITA,
        ]);
    }
}
