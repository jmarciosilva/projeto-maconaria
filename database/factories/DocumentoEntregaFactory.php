<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\StatusEntregaDocumentoTrabalho;
use App\Models\DocumentoAtividade;
use App\Models\DocumentoEntrega;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DocumentoEntrega>
 */
final class DocumentoEntregaFactory extends Factory
{
    protected $model = DocumentoEntrega::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'atividade_id' => DocumentoAtividade::factory()->publicada(),
            'usuario_id' => User::factory(),
            'titulo' => fake()->sentence(4),
            'descricao' => fake()->paragraph(),
            'status' => StatusEntregaDocumentoTrabalho::ENVIADA,
            'enviado_em' => now(),
        ];
    }
}
