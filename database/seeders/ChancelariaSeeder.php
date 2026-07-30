<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\StatusComunicadoChancelaria;
use App\Models\ChancelariaComunicado;
use App\Models\User;
use Illuminate\Database\Seeder;

final class ChancelariaSeeder extends Seeder
{
    public function run(): void
    {
        $autor = User::query()->first();

        if (! $autor) {
            return;
        }

        ChancelariaComunicado::firstOrCreate(
            ['titulo' => 'Comunicado inicial da Chancelaria'],
            [
                'autor_id' => $autor->id,
                'conteudo' => '<p>Comunicado inicial para validação do módulo de Chancelaria.</p>',
                'status' => StatusComunicadoChancelaria::RASCUNHO,
            ],
        );
    }
}
