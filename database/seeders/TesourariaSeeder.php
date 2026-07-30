<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\TipoContaFinanceira;
use App\Enums\TipoLancamentoFinanceiro;
use App\Models\TesourariaCategoria;
use App\Models\TesourariaConta;
use Illuminate\Database\Seeder;

final class TesourariaSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->categorias() as $categoria) {
            TesourariaCategoria::firstOrCreate(
                ['nome' => $categoria['nome'], 'tipo' => $categoria['tipo']],
                ['ativa' => true],
            );
        }

        TesourariaConta::firstOrCreate(
            ['nome' => 'Caixa da Loja'],
            [
                'tipo' => TipoContaFinanceira::CAIXA,
                'saldo_inicial_centavos' => 0,
                'ativa' => true,
            ],
        );
    }

    /**
     * As categorias iniciais cobrem o fluxo mínimo de mensalidades, receitas
     * avulsas e despesas administrativas sem impor um plano de contas definitivo.
     *
     * @return array<int, array{nome: string, tipo: TipoLancamentoFinanceiro}>
     */
    private function categorias(): array
    {
        return [
            ['nome' => 'Mensalidades', 'tipo' => TipoLancamentoFinanceiro::RECEITA],
            ['nome' => 'Doações', 'tipo' => TipoLancamentoFinanceiro::RECEITA],
            ['nome' => 'Eventos', 'tipo' => TipoLancamentoFinanceiro::RECEITA],
            ['nome' => 'Aluguel e manutenção', 'tipo' => TipoLancamentoFinanceiro::DESPESA],
            ['nome' => 'Material administrativo', 'tipo' => TipoLancamentoFinanceiro::DESPESA],
        ];
    }
}
