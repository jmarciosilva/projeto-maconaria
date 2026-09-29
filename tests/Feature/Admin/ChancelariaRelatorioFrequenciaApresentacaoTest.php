<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\SituacaoCadastralIrmao;
use App\Enums\StatusEvento;
use App\Enums\StatusFrequencia;
use App\Enums\TipoEvento;
use App\Enums\VisibilidadeEvento;
use App\Models\ChancelariaFrequencia;
use App\Models\ConfiguracaoInstitucional;
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
 * Apresentação do Relatório de Frequência.
 *
 * Cobre a camada de documento (cabeçalho institucional, resumo, tabela,
 * critério, emissão, assinatura e regras de impressão). As regras de
 * cálculo são cobertas em ChancelariaFrequenciaMvpTest e aqui apenas
 * verificadas como contrato que esta tarefa não podia alterar.
 */
class ChancelariaRelatorioFrequenciaApresentacaoTest extends TestCase
{
    use RefreshDatabase;

    private function chanceler(): User
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create(['name' => 'Chanceler de Teste']);
        $usuario->givePermissionTo('chancelaria.visualizar', 'chancelaria.criar', 'chancelaria.editar');

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
     * Um Irmão com 1 presença em 3 sessões lançadas: 33,3%, abaixo do limite.
     */
    private function relatorioComIrmaoAbaixoDoLimite(): TestResponse
    {
        $usuario = $this->chanceler();
        $irmao = $this->irmao('Irmão Abaixo', '100321');

        foreach ([
            ['2025-01-15 20:00', StatusFrequencia::PRESENTE],
            ['2025-02-15 20:00', StatusFrequencia::AUSENTE],
            ['2025-03-15 20:00', StatusFrequencia::AUSENTE],
        ] as [$data, $status]) {
            ChancelariaFrequencia::create([
                'evento_id' => $this->sessao($data)->id,
                'irmao_id' => $irmao->id,
                'status' => $status,
            ]);
        }

        return $this->actingAs($usuario)->get(route('admin.chancelaria.relatorios.frequencia', [
            'inicio' => '2025-01-01',
            'fim' => '2025-12-31',
        ]));
    }

    // ---------------------------------------------------------------
    // 1 e 2 — acesso e período
    // ---------------------------------------------------------------

    public function test_relatorio_continua_acessivel(): void
    {
        $this->relatorioComIrmaoAbaixoDoLimite()->assertOk();
    }

    public function test_periodo_continua_aparecendo(): void
    {
        $resposta = $this->relatorioComIrmaoAbaixoDoLimite();

        $resposta->assertSee('Período: 01/01/2025 a 31/12/2025');
    }

    public function test_cabecalho_institucional_usa_dados_ja_configurados(): void
    {
        $resposta = $this->relatorioComIrmaoAbaixoDoLimite();

        $resposta->assertSee('Chancelaria');
        $resposta->assertSee('Relatório de Frequência');
        $resposta->assertSee(ConfiguracaoInstitucional::atual()->nome());
    }

    public function test_brasao_usa_asset_local_e_nunca_url_externa(): void
    {
        $resposta = $this->relatorioComIrmaoAbaixoDoLimite();

        $resposta->assertSee('documento__brasao', false);
        // O asset precisa ser servido pela própria aplicação: uma URL externa
        // pode não carregar no momento da impressão.
        $resposta->assertDontSee('https://cdn.', false);
        $this->assertStringContainsString(
            (string) parse_url(config('app.url'), PHP_URL_HOST),
            $resposta->getContent(),
        );
    }

    // ---------------------------------------------------------------
    // 3 — colunas da tabela
    // ---------------------------------------------------------------

    public function test_tabela_contem_as_colunas_esperadas(): void
    {
        $resposta = $this->relatorioComIrmaoAbaixoDoLimite();

        foreach (['CIM', 'Irmão', 'Sessões consideradas', 'Presenças', 'Ausências', 'Justificadas', 'Não informadas', 'Frequência', 'Indicador'] as $coluna) {
            $resposta->assertSee($coluna);
        }
    }

    public function test_tabela_oferece_rotulos_abreviados_para_impressao(): void
    {
        $resposta = $this->relatorioComIrmaoAbaixoDoLimite();

        // Ambos existem no HTML; o CSS de impressão escolhe qual exibir.
        foreach (['Consider.', 'Pres.', 'Aus.', 'Just.', 'N/Inf.', 'Freq.'] as $abreviacao) {
            $resposta->assertSee($abreviacao);
        }

        $resposta->assertSee('rotulo-completo', false);
        $resposta->assertSee('rotulo-curto', false);
    }

    public function test_resumo_apresenta_somente_totais_ja_apurados(): void
    {
        $resposta = $this->relatorioComIrmaoAbaixoDoLimite();

        $resposta->assertSee('Sessões encontradas no período');
        $resposta->assertSee('Irmãos relacionados');
    }

    // ---------------------------------------------------------------
    // 4 e 5 — critério e indicador
    // ---------------------------------------------------------------

    public function test_criterio_continua_presente(): void
    {
        $resposta = $this->relatorioComIrmaoAbaixoDoLimite();

        $resposta->assertSee('Critério deste relatório');
        $resposta->assertSee('lançamento explícito');
        $resposta->assertSee('Não informadas');
        $resposta->assertSee('não integram o cálculo');
    }

    public function test_indicador_abaixo_do_limite_continua_semanticamente_correto(): void
    {
        $resposta = $this->relatorioComIrmaoAbaixoDoLimite();

        $resposta->assertSee('Frequência abaixo de 50%');
        // O indicador é para análise, não decisão institucional.
        $resposta->assertSee('apenas um indicador para análise');
        $resposta->assertSee('não representa');
    }

    // ---------------------------------------------------------------
    // 6 — vocabulário proibido
    // ---------------------------------------------------------------

    public function test_nao_utiliza_termos_de_julgamento_institucional(): void
    {
        $conteudo = mb_strtolower((string) $this->relatorioComIrmaoAbaixoDoLimite()->getContent());

        foreach (['inapto', 'irregular', 'inelegível', 'inelegivel', 'reprovado', 'suspenso', 'impedido'] as $termo) {
            $this->assertStringNotContainsString(
                $termo,
                $conteudo,
                "O relatório não pode emitir julgamento institucional: encontrado \"{$termo}\".",
            );
        }
    }

    // ---------------------------------------------------------------
    // 7 e 8 — impressão
    // ---------------------------------------------------------------

    public function test_botao_de_imprimir_existe_e_usa_window_print(): void
    {
        $resposta = $this->relatorioComIrmaoAbaixoDoLimite();

        $resposta->assertSee('Imprimir / Salvar PDF');
        $resposta->assertSee('window.print()', false);
    }

    public function test_botao_de_imprimir_nao_aparece_antes_de_gerar(): void
    {
        $resposta = $this->actingAs($this->chanceler())
            ->get(route('admin.chancelaria.relatorios.frequencia'));

        $resposta->assertOk();
        $resposta->assertDontSee('Imprimir / Salvar PDF');
    }

    public function test_elementos_administrativos_sao_marcados_para_nao_imprimir(): void
    {
        $resposta = $this->relatorioComIrmaoAbaixoDoLimite();
        $conteudo = (string) $resposta->getContent();

        // Filtros e ações ficam fora do papel.
        $resposta->assertSee('nao-imprimir', false);

        // O corpo carrega o gancho que o CSS usa para neutralizar a moldura.
        $this->assertStringContainsString('layout-admin', $conteudo);

        // Sidebar e barra superior do painel precisam estar marcadas.
        $this->assertGreaterThanOrEqual(
            3,
            substr_count($conteudo, 'nao-imprimir'),
            'Sidebar, menu mobile, barra superior e filtros devem estar marcados como nao-imprimir.',
        );
    }

    public function test_documento_declara_as_secoes_do_papel(): void
    {
        $resposta = $this->relatorioComIrmaoAbaixoDoLimite();

        foreach ([
            'documento__cabecalho',
            'documento__resumo',
            'documento__tabela',
            'documento__criterio',
            'documento__emissao',
            'documento__assinatura',
            'documento__rodape',
        ] as $secao) {
            $resposta->assertSee($secao, false);
        }
    }

    public function test_folha_de_impressao_define_a4_paisagem_e_quebras(): void
    {
        $css = (string) file_get_contents(resource_path('css/impressao.css'));

        $this->assertStringContainsString('size: A4 landscape', $css);
        $this->assertStringContainsString('display: table-header-group', $css);
        $this->assertStringContainsString('break-inside: avoid', $css);
        // A tabela não pode ser cortada horizontalmente no papel.
        $this->assertStringContainsString('overflow: visible !important', $css);
    }

    // ---------------------------------------------------------------
    // 9 a 11 — emissão, assinatura e cálculo intacto
    // ---------------------------------------------------------------

    public function test_emissao_identifica_data_e_responsavel_sem_expor_dados_sensiveis(): void
    {
        $resposta = $this->relatorioComIrmaoAbaixoDoLimite();

        $resposta->assertSee('Emitido em:');
        $resposta->assertSee('Emitido por: Chanceler de Teste');
        // E-mail do usuário não pertence ao documento.
        $resposta->assertDontSee((string) auth()->user()?->email);
    }

    public function test_area_de_assinatura_nao_preenche_nome_automaticamente(): void
    {
        $resposta = $this->relatorioComIrmaoAbaixoDoLimite();
        $conteudo = (string) $resposta->getContent();

        $this->assertStringContainsString('documento__assinatura-linha', $conteudo);

        // A linha de assinatura é rotulada apenas como "Chancelaria": o
        // sistema não sabe quem responde pelo cargo.
        $posicaoAssinatura = mb_strpos($conteudo, 'documento__assinatura-linha');
        $trecho = mb_substr($conteudo, (int) $posicaoAssinatura, 400);

        $this->assertStringNotContainsString('Chanceler de Teste', $trecho);
    }

    public function test_nenhuma_regra_de_calculo_foi_modificada(): void
    {
        $this->assertSame(50.0, CalculadoraFrequencia::LIMITE_INDICADOR);

        $irmao = $this->irmao('Irmão Contrato', '100999');

        $presente = $this->sessao('2025-01-15 20:00');
        $justificado = $this->sessao('2025-02-15 20:00');
        $this->sessao('2025-03-15 20:00'); // sem lançamento: não informado

        ChancelariaFrequencia::create(['evento_id' => $presente->id, 'irmao_id' => $irmao->id, 'status' => StatusFrequencia::PRESENTE]);
        ChancelariaFrequencia::create(['evento_id' => $justificado->id, 'irmao_id' => $irmao->id, 'status' => StatusFrequencia::JUSTIFICADO]);

        $resultado = (new CalculadoraFrequencia)->apurar(
            Carbon::parse('2025-01-01')->startOfDay(),
            Carbon::parse('2025-12-31')->endOfDay(),
        );
        $linha = $resultado['linhas']->firstWhere('irmao_id', $irmao->id);

        $this->assertSame(3, $resultado['total_sessoes']);
        $this->assertSame(2, $linha['consideradas'], 'Só o lançamento explícito entra no denominador.');
        $this->assertSame(1, $linha['justificadas']);
        $this->assertSame(1, $linha['nao_informadas'], 'Sessão sem lançamento não integra o cálculo.');
        $this->assertSame(50.0, $linha['percentual'], 'Justificado conta como ausência para o percentual.');
        $this->assertFalse($linha['abaixo_do_limite'], '50% não é abaixo do limite.');
    }
}
