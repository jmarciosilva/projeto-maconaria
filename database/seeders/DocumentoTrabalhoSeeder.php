<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\StatusDocumentoTrabalho;
use App\Models\DocumentoAtividade;
use App\Models\User;
use Illuminate\Database\Seeder;

final class DocumentoTrabalhoSeeder extends Seeder
{
    public function run(): void
    {
        $autor = User::query()->first();

        if (! $autor) {
            return;
        }

        DocumentoAtividade::firstOrCreate(
            ['titulo' => 'Leitura orientada inicial'],
            [
                'autor_id' => $autor->id,
                'descricao' => 'Atividade inicial para validar o módulo de documentos e trabalhos.',
                'status' => StatusDocumentoTrabalho::PUBLICADA,
                'publicado_em' => now(),
                'prazo_entrega_em' => now()->addDays(30),
            ],
        );
    }
}
