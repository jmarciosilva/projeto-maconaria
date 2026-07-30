<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['ano', 'mes', 'fechado_por_id', 'fechado_em', 'observacao'])]
final class TesourariaFechamento extends Model
{
    protected $table = 'tesouraria_fechamentos';

    protected function casts(): array
    {
        return [
            'ano' => 'integer',
            'mes' => 'integer',
            'fechado_em' => 'datetime',
        ];
    }
}
