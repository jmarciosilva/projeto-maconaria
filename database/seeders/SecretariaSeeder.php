<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\StatusDocumentoSecretaria;
use App\Enums\TipoDocumentoSecretaria;
use App\Models\SecretariaDocumento;
use App\Models\SecretariaNumerador;
use App\Models\User;
use Illuminate\Database\Seeder;

final class SecretariaSeeder extends Seeder
{
    public function run(): void
    {
        $autor = User::query()->first();

        if (! $autor) {
            return;
        }

        SecretariaDocumento::firstOrCreate(
            ['codigo' => 'ATA-'.now()->format('Y').'-0001'],
            [
                'autor_id' => $autor->id,
                'tipo' => TipoDocumentoSecretaria::ATA,
                'ano' => (int) now()->year,
                'numero' => 1,
                'titulo' => 'Ata inicial de referência',
                'conteudo' => 'Documento inicial criado para validar o módulo de Secretaria.',
                'status' => StatusDocumentoSecretaria::RASCUNHO,
                'data_documento' => now()->toDateString(),
            ],
        );

        SecretariaNumerador::updateOrCreate(
            ['tipo' => TipoDocumentoSecretaria::ATA, 'ano' => (int) now()->year],
            ['proximo_numero' => 2],
        );
    }
}
