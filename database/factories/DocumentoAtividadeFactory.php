<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\StatusDocumentoTrabalho;
use App\Models\DocumentoAtividade;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DocumentoAtividade>
 */
final class DocumentoAtividadeFactory extends Factory
{
    protected $model = DocumentoAtividade::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'autor_id' => User::factory(),
            'titulo' => fake()->sentence(4),
            'descricao' => fake()->paragraph(),
            'status' => StatusDocumentoTrabalho::RASCUNHO,
            'prazo_entrega_em' => now()->addDays(15),
        ];
    }

    public function publicada(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StatusDocumentoTrabalho::PUBLICADA,
            'publicado_em' => now(),
        ]);
    }
}
