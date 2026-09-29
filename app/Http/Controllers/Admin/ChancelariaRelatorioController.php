<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Chancelaria\CalculadoraFrequencia;
use App\Support\RegistradorDeAuditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

final class ChancelariaRelatorioController extends Controller
{
    public function frequencia(Request $request, CalculadoraFrequencia $calculadora): View
    {
        $this->authorize('chancelaria.visualizar');

        $dados = $request->validate([
            'inicio' => ['nullable', 'date'],
            'fim' => ['nullable', 'date', 'after_or_equal:inicio'],
            'ordenar' => ['nullable', 'in:nome,frequencia'],
        ]);

        $gerou = filled($dados['inicio'] ?? null) && filled($dados['fim'] ?? null);

        $inicio = Carbon::parse($dados['inicio'] ?? now()->startOfYear())->startOfDay();
        $fim = Carbon::parse($dados['fim'] ?? now()->endOfYear())->endOfDay();
        $ordenar = $dados['ordenar'] ?? 'nome';

        $resultado = ['total_sessoes' => 0, 'linhas' => collect()];

        if ($gerou) {
            $resultado = $calculadora->apurar($inicio, $fim, $ordenar);

            RegistradorDeAuditoria::registrar(
                acao: 'gerar-relatorio-frequencia',
                modulo: 'chancelaria',
                dadosNovos: [
                    'inicio' => $inicio->toDateString(),
                    'fim' => $fim->toDateString(),
                    'total_sessoes' => $resultado['total_sessoes'],
                ],
            );
        }

        return view('admin.chancelaria.relatorios.frequencia', [
            'gerou' => $gerou,
            'inicio' => $inicio,
            'fim' => $fim,
            'ordenar' => $ordenar,
            'totalSessoes' => $resultado['total_sessoes'],
            'linhas' => $resultado['linhas'],
            'limiteIndicador' => CalculadoraFrequencia::LIMITE_INDICADOR,
        ]);
    }
}
