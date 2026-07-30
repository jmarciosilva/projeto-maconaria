<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\StatusNoticia;
use App\Enums\VisibilidadeNoticia;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'noticia_id',
    'usuario_id',
    'versao',
    'titulo',
    'resumo',
    'conteudo',
    'status',
    'visibilidade',
    'destaque',
    'publicado_em',
    'agendado_para',
])]
final class NoticiaVersao extends Model
{
    protected $table = 'noticia_versoes';

    protected function casts(): array
    {
        return [
            'status' => StatusNoticia::class,
            'visibilidade' => VisibilidadeNoticia::class,
            'destaque' => 'boolean',
            'publicado_em' => 'datetime',
            'agendado_para' => 'datetime',
        ];
    }

    public function noticia(): BelongsTo
    {
        return $this->belongsTo(Noticia::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
