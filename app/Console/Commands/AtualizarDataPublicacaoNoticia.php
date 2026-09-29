<?php

namespace App\Console\Commands;

use App\Models\Noticia;
use Carbon\Carbon;
use Illuminate\Console\Command;

final class AtualizarDataPublicacaoNoticia extends Command
{
    protected $signature = 'noticia:atualizar-data {noticia : ID ou slug da notícia} {data : Data e hora (formato: Y-m-d H:i)}';
    protected $description = 'Atualiza a data de publicação de uma notícia';

    public function handle(): int
    {
        $noticiaId = $this->argument('noticia');
        $dataStr = $this->argument('data');

        $noticia = is_numeric($noticiaId)
            ? Noticia::find($noticiaId)
            : Noticia::where('slug', $noticiaId)->first();

        if (!$noticia) {
            $this->error("Notícia não encontrada: {$noticiaId}");
            return 1;
        }

        try {
            $data = Carbon::createFromFormat('Y-m-d H:i', $dataStr);
        } catch (\Exception $e) {
            $this->error("Formato de data inválido. Use: Y-m-d H:i (ex: 2024-01-15 10:30)");
            return 1;
        }

        $noticia->update(['publicado_em' => $data]);

        $this->info("✓ Data de publicação atualizada!");
        $this->line("Notícia: {$noticia->titulo}");
        $this->line("Data: " . $data->format('d/m/Y H:i'));

        return 0;
    }
}
