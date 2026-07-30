<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\StatusEvento;
use App\Enums\TipoEvento;
use App\Enums\VisibilidadeEvento;
use App\Models\Evento;
use App\Models\User;
use Illuminate\Database\Seeder;

final class EventoSeeder extends Seeder
{
    public function run(): void
    {
        $autor = User::query()->first();

        if (! $autor) {
            return;
        }

        Evento::firstOrCreate(
            ['slug' => 'sessao-ordinaria'],
            [
                'autor_id' => $autor->id,
                'titulo' => 'Sessão ordinária',
                'descricao' => 'Sessão restrita aos Irmãos da Loja.',
                'tipo' => TipoEvento::SESSAO,
                'status' => StatusEvento::PUBLICADO,
                'visibilidade' => VisibilidadeEvento::RESTRITA,
                'local' => 'Templo da Loja',
                'inicio_em' => now()->addWeek()->setTime(20, 0),
                'fim_em' => now()->addWeek()->setTime(22, 0),
                'inscricoes_ate' => now()->addWeek()->subDay()->setTime(18, 0),
                'permite_confirmacao' => true,
            ],
        );

        Evento::firstOrCreate(
            ['slug' => 'palestra-publica'],
            [
                'autor_id' => $autor->id,
                'titulo' => 'Palestra pública',
                'descricao' => 'Evento aberto ao público interessado em conhecer a atuação institucional da Loja.',
                'tipo' => TipoEvento::EVENTO,
                'status' => StatusEvento::PUBLICADO,
                'visibilidade' => VisibilidadeEvento::PUBLICA,
                'local' => 'Sede da Loja',
                'inicio_em' => now()->addWeeks(2)->setTime(19, 30),
                'fim_em' => now()->addWeeks(2)->setTime(21, 0),
                'permite_confirmacao' => false,
            ],
        );
    }
}
