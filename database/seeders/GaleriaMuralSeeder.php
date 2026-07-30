<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\StatusMuralGaleria;
use App\Enums\VisibilidadeMuralGaleria;
use App\Models\GaleriaAlbum;
use App\Models\MuralPublicacao;
use App\Models\User;
use Illuminate\Database\Seeder;

final class GaleriaMuralSeeder extends Seeder
{
    public function run(): void
    {
        $autor = User::query()->first();

        if (! $autor) {
            return;
        }

        GaleriaAlbum::firstOrCreate(
            ['slug' => 'album-inicial'],
            [
                'autor_id' => $autor->id,
                'titulo' => 'Álbum inicial',
                'descricao' => 'Álbum inicial para validar a Galeria da Loja.',
                'status' => StatusMuralGaleria::PUBLICADO,
                'visibilidade' => VisibilidadeMuralGaleria::PUBLICA,
                'publicado_em' => now(),
            ],
        );

        MuralPublicacao::firstOrCreate(
            ['titulo' => 'Publicação inicial do mural'],
            [
                'autor_id' => $autor->id,
                'conteudo' => 'Publicação inicial para validar o Mural da Loja.',
                'status' => StatusMuralGaleria::PUBLICADO,
                'visibilidade' => VisibilidadeMuralGaleria::RESTRITA,
                'publicado_em' => now(),
            ],
        );
    }
}
