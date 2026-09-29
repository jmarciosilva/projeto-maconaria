<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConfiguracaoInstitucional;
use App\Support\Chancelaria\CalculadoraFrequencia;
use App\Support\Pdf\GeradorPdf;
use App\Support\RegistradorDeAuditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

final class ChancelariaRelatorioController extends Controller
{
    public function frequencia(Request $request, CalculadoraFrequencia $calculadora): View
    {
        $this->authorize('chancelaria.visualizar');

        $relatorio = $this->apurarFrequencia($request, $calculadora);

        if ($relatorio['gerou']) {
            $this->registrarAuditoria('gerar-relatorio-frequencia', $relatorio);
        }

        return view('admin.chancelaria.relatorios.frequencia', $relatorio);
    }

    /**
     * Mesma apuração da tela, entregue como documento PDF.
     */
    public function frequenciaPdf(Request $request, CalculadoraFrequencia $calculadora): Response|RedirectResponse
    {
        $this->authorize('chancelaria.visualizar');

        $relatorio = $this->apurarFrequencia($request, $calculadora);

        // Sem período não há documento a emitir: volta para a tela, onde o
        // usuário escolhe as datas.
        if (! $relatorio['gerou']) {
            return redirect()->route('admin.chancelaria.relatorios.frequencia');
        }

        $this->registrarAuditoria('gerar-relatorio-frequencia-pdf', $relatorio);

        $conteudo = GeradorPdf::deView(
            'admin.chancelaria.relatorios.frequencia-pdf',
            $relatorio + [
                'configuracaoInstitucional' => ConfiguracaoInstitucional::atual(),
                'brasao' => $this->caminhoDoBrasao(),
                'emitidoPor' => $request->user()?->name,
                'emitidoEm' => now(),
            ],
            orientacao: 'landscape',
            rodape: 'Relatório de Frequência — Chancelaria · '.ConfiguracaoInstitucional::atual()->nome(),
        );

        return response($conteudo, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$this->nomeDoArquivo($relatorio).'"',
        ]);
    }

    /**
     * Preparação única dos dados do relatório: tela e PDF consomem este
     * mesmo retorno, para que não exista uma segunda versão da apuração.
     *
     * @return array<string, mixed>
     */
    private function apurarFrequencia(Request $request, CalculadoraFrequencia $calculadora): array
    {
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
        }

        return [
            'gerou' => $gerou,
            'inicio' => $inicio,
            'fim' => $fim,
            'ordenar' => $ordenar,
            'totalSessoes' => $resultado['total_sessoes'],
            'linhas' => $resultado['linhas'],
            'limiteIndicador' => CalculadoraFrequencia::LIMITE_INDICADOR,
        ];
    }

    /**
     * @param  array<string, mixed>  $relatorio
     */
    private function registrarAuditoria(string $acao, array $relatorio): void
    {
        RegistradorDeAuditoria::registrar(
            acao: $acao,
            modulo: 'chancelaria',
            dadosNovos: [
                'inicio' => $relatorio['inicio']->toDateString(),
                'fim' => $relatorio['fim']->toDateString(),
                'total_sessoes' => $relatorio['totalSessoes'],
            ],
        );
    }

    /**
     * Caminho local do brasão. O DomPDF roda no servidor e não busca URL da
     * própria aplicação, então precisa do arquivo em disco. Prioriza o
     * logotipo enviado pelo administrador e cai no selo versionado quando
     * não há nenhum — ou quando o registro aponta para arquivo inexistente.
     */
    private function caminhoDoBrasao(): ?string
    {
        $logotipo = ConfiguracaoInstitucional::atual()->logotipo;

        if (filled($logotipo)) {
            $caminho = Storage::disk('public')->path($logotipo);

            if (is_file($caminho)) {
                return $caminho;
            }
        }

        $fallback = public_path('images/logo-loja.png');

        return is_file($fallback) ? $fallback : null;
    }

    /**
     * @param  array<string, mixed>  $relatorio
     */
    private function nomeDoArquivo(array $relatorio): string
    {
        $nome = sprintf(
            'relatorio-frequencia-%s-a-%s.pdf',
            $relatorio['inicio']->format('Y-m-d'),
            $relatorio['fim']->format('Y-m-d'),
        );

        // As datas já vêm formatadas pelo Carbon, mas a higienização fica
        // explícita para que o cabeçalho continue seguro se a composição do
        // nome mudar um dia.
        return (string) preg_replace('/[^A-Za-z0-9._-]/', '', $nome);
    }
}
