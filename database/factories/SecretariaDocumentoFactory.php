<?php

namespace Database\Factories;

use App\Enums\StatusDocumentoSecretaria;
use App\Enums\TipoDocumentoSecretaria;
use App\Models\SecretariaDocumento;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SecretariaDocumento>
 */
class SecretariaDocumentoFactory extends Factory
{
    public function definition(): array
    {
        $ano = (int) now()->year;
        $numero = fake()->unique()->numberBetween(1, 9999);
        $tipo = TipoDocumentoSecretaria::ATA;

        return [
            'autor_id' => User::factory(),
            'tipo' => $tipo,
            'ano' => $ano,
            'numero' => $numero,
            'codigo' => sprintf('%s-%04d-%04d', $tipo->prefixo(), $ano, $numero),
            'titulo' => fake()->sentence(4),
            'conteudo' => fake()->paragraphs(3, true),
            'status' => StatusDocumentoSecretaria::RASCUNHO,
            'data_documento' => now()->toDateString(),
        ];
    }

    public function emAprovacao(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StatusDocumentoSecretaria::EM_APROVACAO,
        ]);
    }

    public function aprovado(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StatusDocumentoSecretaria::APROVADO,
            'aprovado_em' => now(),
        ]);
    }
}
