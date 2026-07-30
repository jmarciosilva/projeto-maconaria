<?php

declare(strict_types=1);

namespace App\Support\Tesouraria;

use App\Exceptions\PeriodoFinanceiroFechadoException;
use App\Models\TesourariaFechamento;
use Carbon\CarbonInterface;

final class ValidadorPeriodoFinanceiro
{
    public function garantirAberto(CarbonInterface $data): void
    {
        $fechado = TesourariaFechamento::query()
            ->where('ano', (int) $data->format('Y'))
            ->where('mes', (int) $data->format('m'))
            ->exists();

        if ($fechado) {
            throw new PeriodoFinanceiroFechadoException('O período financeiro informado já está fechado.');
        }
    }
}
