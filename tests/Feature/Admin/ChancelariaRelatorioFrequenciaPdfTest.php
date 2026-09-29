<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\SituacaoCadastralIrmao;
use App\Enums\StatusEvento;
use App\Enums\StatusFrequencia;
use App\Enums\TipoEvento;
use App\Enums\VisibilidadeEvento;
use App\Models\ChancelariaFrequencia;
use App\Models\Evento;
use App\Models\Irmao;
use App\Models\User;
use App\Support\Chancelaria\CalculadoraFrequencia;
use Database\Seeders\PerfilPermissaoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Geração server-side do Relatório de Frequência em PDF.
 *
 * O PDF é uma segunda saída da mesma apuração exibida na tela: estes testes
 * verificam o transporte (autorização, cabeçalhos, nome de arquivo, bytes
 * realmente em PDF) e o conteúdo, sem reimplementar a regra de frequência.
 */
class ChancelariaRelatorioFrequenciaPdfTest extends TestCase
{
    use RefreshDatabase;

    private function chanceler(): User
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create(['name' => 'José Márcio Ferreira da Silva']);
        $usuario->givePermissionTo('chancelaria.visualizar');

        return $usuario;
    }

    private function sessao(string $data): Evento
    {
        return Evento::factory()->create([
            'tipo' => TipoEvento::SESSAO,
            'status' => StatusEvento::PUBLICADO,
            'visibilidade' => VisibilidadeEvento::RESTRITA,
            'inicio_em' => Carbon::parse($data),
        ]);
    }

    private function irmao(string $nome, ?string $cim = null): Irmao
    {
        return Irmao::factory()->create([
            'nome_completo' => $nome,
            'cim' => $cim,
            'cpf' => null,
            'situacao_cadastral' => SituacaoCadastralIrmao::ATIVO,
        ]);
    }

    /**
     * Cenário compartilhado: um Irmão com 1 presença em 3 lançamentos
     * (33,3%, abaixo do limite) e outro com presença integral.
     */
    private function cenario(): void
    {
        $abaixo = $this->irmao('João Carlos dos Santos', '100321');
        $regular = $this->irmao("Ana-Maria D'Ávila", '100322');

        $sessoes = [
            $this->sessao('2025-01-15 20:00'),
            $this->sessao('2025-02-15 20:00'),
            $this->sessao('2025-03-15 20:00'),
        ];

        foreach ([StatusFrequencia::PRESENTE, StatusFrequencia::AUSENTE, StatusFrequencia::AUSENTE] as $i => $status) {
            ChancelariaFrequencia::create([
                'evento_id' => $sessoes[$i]->id,
                'irmao_id' => $abaixo->id,
                'status' => $status,
            ]);
        }

        foreach ($sessoes as $sessao) {
            ChancelariaFrequencia::create([
                'evento_id' => $sessao->id,
                'irmao_id' => $regular->id,
                'status' => StatusFrequencia::PRESENTE,
            ]);
        }
    }

    private function baixarPdf(?User $usuario = null, array $filtros = []): TestResponse
    {
        $requisicao = $usuario ? $this->actingAs($usuario) : $this;

        return $requisicao->get(route('admin.chancelaria.relatorios.frequencia.pdf', $filtros + [
            'inicio' => '2025-01-01',
            'fim' => '2025-12-31',
        ]));
    }

    /**
     * Extrai o texto do PDF decodificando os literais das operações de texto.
     * O DomPDF grava as strings em UTF-16BE, então dá para conferir o
     * conteúdo sem depender de ferramenta externa.
     */
    private function textoDoPdf(string $pdf): string
    {
        $paginas = [];

        preg_match_all('/(?<!end)stream[\r\n]{1,2}/', $pdf, $marcas, PREG_OFFSET_CAPTURE);

        foreach ($marcas[0] as $marca) {
            $inicio = $marca[1] + strlen($marca[0]);
            $fim = strpos($pdf, 'endstream', $inicio);

            if ($fim === false) {
                continue;
            }

            $conteudo = @gzuncompress(substr($pdf, $inicio, $fim - $inicio));

            if ($conteudo === false || ! str_contains($conteudo, 'BT')) {
                continue;
            }

            preg_match_all('/\(((?:[^()\\\\]|\\\\.)*)\)/s', $conteudo, $literais);

            $bytes = '';
            foreach ($literais[1] as $literal) {
                $bytes .= preg_replace_callback(
                    '/\\\\([nrtbf()\\\\]|[0-7]{1,3})/',
                    static fn (array $e): string => match ($e[1]) {
                        'n' => "\n", 'r' => "\r", 't' => "\t", 'b' => "\x08", 'f' => "\x0C",
                        default => ctype_digit($e[1]) ? chr(octdec($e[1]) & 0xFF) : $e[1],
                    },
                    $literal,
                );
            }

            $paginas[] = (string) mb_convert_encoding($bytes, 'UTF-8', 'UTF-16BE');
        }

        return implode("\n", $paginas);
    }

    /**
     * O DomPDF distribui uma mesma frase por vários elementos do array TJ,
     * aplicando kerning entre eles; os espaços dessas junções não existem
     * como caractere. Comparar sem espaço nenhum evita que o teste acuse
     * erro por um artefato da extração, e não do documento.
     */
    private function assertPdfContem(string $texto, string $trecho, string $mensagem = ''): void
    {
        $this->assertStringContainsString(
            $this->comparavel($trecho),
            $this->comparavel($texto),
            $mensagem !== '' ? $mensagem : "Esperava encontrar \"{$trecho}\" no PDF.",
        );
    }

    private function assertPdfNaoContem(string $texto, string $trecho, string $mensagem = ''): void
    {
        $this->assertStringNotContainsString(
            $this->comparavel($trecho),
            $this->comparavel($texto),
            $mensagem !== '' ? $mensagem : "Não esperava encontrar \"{$trecho}\" no PDF.",
        );
    }

    /**
     * Também ignora caixa, porque o documento aplica text-transform:
     * uppercase em títulos e cabeçalhos de coluna — o texto extraído sai
     * em maiúsculas, embora a view escreva em caixa mista.
     */
    private function comparavel(string $valor): string
    {
        return mb_strtolower((string) preg_replace('/\s+/u', '', $valor));
    }

    // ---------------------------------------------------------------
    // 1 e 2 — segurança
    // ---------------------------------------------------------------

    public function test_usuario_nao_autenticado_nao_acessa_o_pdf(): void
    {
        $this->cenario();

        $this->baixarPdf()->assertRedirect(route('login'));
    }

    public function test_usuario_sem_permissao_nao_acessa_o_pdf(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);
        $this->cenario();

        $this->baixarPdf(User::factory()->create())->assertForbidden();
    }

    public function test_pdf_exige_a_mesma_permissao_da_tela_sem_criar_permissao_nova(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);
        $this->cenario();

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('chancelaria.visualizar');

        $this->baixarPdf($usuario)->assertOk();
    }

    // ---------------------------------------------------------------
    // 3 a 5 — resposta HTTP
    // ---------------------------------------------------------------

    public function test_usuario_autorizado_recebe_pdf_com_content_type_correto(): void
    {
        $this->cenario();

        $resposta = $this->baixarPdf($this->chanceler());

        $resposta->assertOk();
        $resposta->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_content_disposition_traz_nome_de_arquivo_com_o_periodo(): void
    {
        $this->cenario();

        $disposition = $this->baixarPdf($this->chanceler())->headers->get('Content-Disposition');

        $this->assertStringContainsString('relatorio-frequencia-2025-01-01-a-2025-12-31.pdf', (string) $disposition);
    }

    public function test_nome_do_arquivo_acompanha_o_periodo_informado(): void
    {
        $this->cenario();

        $disposition = $this->actingAs($this->chanceler())
            ->get(route('admin.chancelaria.relatorios.frequencia.pdf', [
                'inicio' => '2025-03-01',
                'fim' => '2025-06-30',
            ]))
            ->headers->get('Content-Disposition');

        $this->assertStringContainsString('relatorio-frequencia-2025-03-01-a-2025-06-30.pdf', (string) $disposition);
    }

    // ---------------------------------------------------------------
    // 9 — PDF de verdade, não HTML renomeado
    // ---------------------------------------------------------------

    public function test_resposta_e_um_pdf_real_e_nao_html_renomeado(): void
    {
        $this->cenario();

        $conteudo = (string) $this->baixarPdf($this->chanceler())->getContent();

        $this->assertStringStartsWith('%PDF', $conteudo, 'O corpo precisa começar com a assinatura %PDF.');
        $this->assertStringContainsString('%%EOF', $conteudo);
        $this->assertStringNotContainsString('<!DOCTYPE html>', $conteudo);
        $this->assertGreaterThan(1000, strlen($conteudo), 'Um PDF com tabela não pode ter poucos bytes.');
    }

    // ---------------------------------------------------------------
    // 6, 7 e 10 — conteúdo, filtros e UTF-8
    // ---------------------------------------------------------------

    public function test_documento_traz_as_secoes_institucionais(): void
    {
        $this->cenario();

        $texto = $this->textoDoPdf((string) $this->baixarPdf($this->chanceler())->getContent());

        $this->assertPdfContem(mb_strtoupper($texto), 'CHANCELARIA');
        $this->assertPdfContem(mb_strtoupper($texto), 'RELATÓRIO DE FREQUÊNCIA');
        $this->assertPdfContem($texto, 'Período:');
        $this->assertPdfContem($texto, '01/01/2025');
        $this->assertPdfContem($texto, 'Critério deste relatório');
        $this->assertPdfContem($texto, 'Emitido em:');
    }

    public function test_utf8_nao_quebra_a_geracao(): void
    {
        $this->cenario();

        $texto = $this->textoDoPdf((string) $this->baixarPdf($this->chanceler())->getContent());

        foreach (['João Carlos dos Santos', "Ana-Maria D'Ávila", 'Frequência', 'justificados', 'Não informadas', 'José Márcio'] as $trecho) {
            $this->assertPdfContem($texto, $trecho, "Acentuação perdida em \"{$trecho}\".");
        }
    }

    public function test_tabela_traz_as_colunas_e_o_indicador(): void
    {
        $this->cenario();

        $texto = $this->textoDoPdf((string) $this->baixarPdf($this->chanceler())->getContent());

        foreach (['CIM', 'Irmão', 'Consider.', 'Pres.', 'Aus.', 'Just.', 'N/Inf.', 'Freq.', 'Indicador'] as $coluna) {
            $this->assertPdfContem($texto, $coluna);
        }

        // Preserva exatamente o indicador da tela, com o símbolo de atenção.
        $this->assertPdfContem($texto, '⚠ Frequência abaixo de 50%');
    }

    public function test_pdf_nao_emite_julgamento_institucional(): void
    {
        $this->cenario();

        $texto = mb_strtolower($this->textoDoPdf((string) $this->baixarPdf($this->chanceler())->getContent()));

        foreach (['inapto', 'irregular', 'inelegível', 'reprovado', 'suspenso', 'impedido'] as $termo) {
            $this->assertPdfNaoContem($texto, $termo);
        }
    }

    public function test_emissao_identifica_o_responsavel_sem_expor_dados_sensiveis(): void
    {
        $this->cenario();
        $usuario = $this->chanceler();

        $texto = $this->textoDoPdf((string) $this->baixarPdf($usuario)->getContent());

        $this->assertPdfContem($texto, 'Emitido em:');
        $this->assertPdfContem($texto, 'Emitido por: José Márcio Ferreira da Silva');
        $this->assertPdfNaoContem($texto, $usuario->email);
    }

    public function test_paginacao_do_engine_numera_as_paginas(): void
    {
        $this->cenario();

        $texto = $this->textoDoPdf((string) $this->baixarPdf($this->chanceler())->getContent());

        $this->assertMatchesRegularExpression('/Página\s*1\s*de\s*\d+/u', $texto);

        // Documento pequeno precisa caber em uma única página.
        $this->assertPdfContem($texto, 'Página 1 de 1', 'Um relatório de 2 Irmãos não deve transbordar para a segunda página.');
    }

    /**
     * Com muitos Irmãos o documento passa de uma página: o cabeçalho da
     * tabela precisa reaparecer e o rodapé institucional precisa estar em
     * todas elas.
     */
    public function test_documento_longo_repete_cabecalho_da_tabela_e_rodape(): void
    {
        $sessao = $this->sessao('2025-01-15 20:00');

        for ($i = 1; $i <= 60; $i++) {
            $irmao = $this->irmao("Irmão Número {$i} de Teste", str_pad((string) (100000 + $i), 6, '0', STR_PAD_LEFT));
            ChancelariaFrequencia::create([
                'evento_id' => $sessao->id,
                'irmao_id' => $irmao->id,
                'status' => StatusFrequencia::PRESENTE,
            ]);
        }

        $conteudo = (string) $this->baixarPdf($this->chanceler())->getContent();
        $texto = $this->textoDoPdf($conteudo);
        $paginas = max(1, preg_match_all('/\/Type\s*\/Page[^s]/', $conteudo));

        $this->assertGreaterThan(1, $paginas, 'O cenário precisa render mais de uma página.');

        $comparavel = $this->comparavel($texto);
        $this->assertSame(
            $paginas,
            substr_count($comparavel, $this->comparavel('Consider.Pres.Aus.Just.')),
            'O cabeçalho da tabela deve se repetir em todas as páginas.',
        );
        $this->assertSame(
            $paginas,
            substr_count($comparavel, $this->comparavel('Relatório de Frequência — Chancelaria')),
            'O rodapé institucional deve aparecer em todas as páginas.',
        );
        $this->assertPdfContem($texto, "Página {$paginas} de {$paginas}");
    }

    public function test_periodo_informado_e_respeitado(): void
    {
        $this->cenario();

        // 2024 não tem sessões: o documento sai sem nenhuma sessão apurada.
        $texto = $this->textoDoPdf((string) $this->actingAs($this->chanceler())
            ->get(route('admin.chancelaria.relatorios.frequencia.pdf', [
                'inicio' => '2024-01-01',
                'fim' => '2024-12-31',
            ]))
            ->assertOk()
            ->getContent());

        $this->assertPdfContem($texto, '01/01/2024');
        // Sem sessões em 2024, ninguém tem percentual apurado.
        $this->assertPdfContem($texto, '— Sem dados');
        $this->assertPdfNaoContem($texto, '33,3%');
        // A frase do critério permanece; o que não pode existir é o selo na
        // coluna Indicador.
        $this->assertPdfNaoContem($texto, '⚠ Frequência abaixo de 50%');
    }

    public function test_pdf_sem_periodo_volta_para_a_tela(): void
    {
        $this->cenario();

        $this->actingAs($this->chanceler())
            ->get(route('admin.chancelaria.relatorios.frequencia.pdf'))
            ->assertRedirect(route('admin.chancelaria.relatorios.frequencia'));
    }

    // ---------------------------------------------------------------
    // 7 e 8 — mesma apuração, regra intacta
    // ---------------------------------------------------------------

    public function test_pdf_usa_a_mesma_apuracao_da_tela(): void
    {
        $this->cenario();
        $usuario = $this->chanceler();

        $tela = $this->actingAs($usuario)->get(route('admin.chancelaria.relatorios.frequencia', [
            'inicio' => '2025-01-01',
            'fim' => '2025-12-31',
        ]))->assertOk();

        $linhas = $tela->viewData('linhas');
        $texto = $this->textoDoPdf((string) $this->baixarPdf($usuario)->getContent());

        $this->assertCount(2, $linhas);

        foreach ($linhas as $linha) {
            $this->assertPdfContem($texto, $linha['nome']);
            $this->assertPdfContem(
                $texto,
                number_format((float) $linha['percentual'], 1, ',', '.').'%',
                'O percentual do PDF precisa ser exatamente o da tela.',
            );
        }

        // Totais idênticos nas duas saídas.
        $this->assertPdfContem($texto, (string) $tela->viewData('totalSessoes'));
    }

    public function test_pdf_registra_auditoria_propria(): void
    {
        $this->cenario();

        $this->baixarPdf($this->chanceler())->assertOk();

        $this->assertDatabaseHas('auditorias', [
            'modulo' => 'chancelaria',
            'acao' => 'gerar-relatorio-frequencia-pdf',
        ]);
    }

    public function test_nenhuma_regra_de_frequencia_foi_modificada(): void
    {
        $this->assertSame(50.0, CalculadoraFrequencia::LIMITE_INDICADOR);

        $irmao = $this->irmao('Irmão Contrato', '100999');

        $presente = $this->sessao('2025-01-15 20:00');
        $justificado = $this->sessao('2025-02-15 20:00');
        $this->sessao('2025-03-15 20:00'); // sem lançamento

        ChancelariaFrequencia::create(['evento_id' => $presente->id, 'irmao_id' => $irmao->id, 'status' => StatusFrequencia::PRESENTE]);
        ChancelariaFrequencia::create(['evento_id' => $justificado->id, 'irmao_id' => $irmao->id, 'status' => StatusFrequencia::JUSTIFICADO]);

        $linha = (new CalculadoraFrequencia)
            ->apurar(Carbon::parse('2025-01-01')->startOfDay(), Carbon::parse('2025-12-31')->endOfDay())['linhas']
            ->firstWhere('irmao_id', $irmao->id);

        $this->assertSame(2, $linha['consideradas']);
        $this->assertSame(1, $linha['justificadas']);
        $this->assertSame(1, $linha['nao_informadas']);
        $this->assertSame(50.0, $linha['percentual']);
        $this->assertFalse($linha['abaixo_do_limite']);
    }

    public function test_nenhum_arquivo_publico_permanente_e_criado(): void
    {
        $this->cenario();

        $antes = glob(public_path('*.pdf')) ?: [];
        $this->baixarPdf($this->chanceler())->assertOk();
        $depois = glob(public_path('*.pdf')) ?: [];

        $this->assertSame($antes, $depois, 'O PDF é servido em memória: nada pode ser gravado em public/.');
        $this->assertFileDoesNotExist(storage_path('app/public/relatorio-frequencia.pdf'));
    }
}
