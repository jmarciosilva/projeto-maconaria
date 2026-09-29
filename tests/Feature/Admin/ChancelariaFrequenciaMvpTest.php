<?php

namespace Tests\Feature\Admin;

use App\Enums\ClasseSessao;
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
use Tests\TestCase;

/**
 * MVP de frequência histórica da Chancelaria.
 */
class ChancelariaFrequenciaMvpTest extends TestCase
{
    use RefreshDatabase;

    private function chanceler(): User
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
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

    // ---------------------------------------------------------------
    // 1 a 4 — cadastro parcial do Irmão
    // ---------------------------------------------------------------

    public function test_irmao_pode_ser_criado_somente_com_nome_e_cim(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);
        $usuario = User::factory()->create();
        $usuario->givePermissionTo('irmaos.criar');

        $this->actingAs($usuario)->post(route('admin.irmaos.store'), [
            'nome_completo' => 'Irmão Sem CPF',
            'cim' => '519328',
            'situacao_cadastral' => SituacaoCadastralIrmao::ATIVO->value,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('irmaos', ['nome_completo' => 'Irmão Sem CPF', 'cim' => '519328', 'cpf' => null]);
    }

    public function test_varios_irmaos_podem_coexistir_sem_cpf(): void
    {
        // O índice único de cpf é mantido; vários NULL são permitidos.
        $this->irmao('Primeiro Sem CPF', '111');
        $this->irmao('Segundo Sem CPF', '222');

        $this->assertSame(2, Irmao::query()->whereNull('cpf')->count());
    }

    public function test_cpf_informado_continua_sendo_validado(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);
        $usuario = User::factory()->create();
        $usuario->givePermissionTo('irmaos.criar');

        $this->actingAs($usuario)->post(route('admin.irmaos.store'), [
            'nome_completo' => 'Irmão CPF Inválido',
            'cpf' => '11111111111',
            'situacao_cadastral' => SituacaoCadastralIrmao::ATIVO->value,
        ])->assertSessionHasErrors('cpf');
    }

    public function test_cpf_informado_continua_unico(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);
        $usuario = User::factory()->create();
        $usuario->givePermissionTo('irmaos.criar');

        $existente = Irmao::factory()->create(['cpf' => '39053344705']);

        $this->actingAs($usuario)->post(route('admin.irmaos.store'), [
            'nome_completo' => 'Outro Irmão',
            'cpf' => $existente->cpf,
            'situacao_cadastral' => SituacaoCadastralIrmao::ATIVO->value,
        ])->assertSessionHasErrors('cpf');
    }

    public function test_cim_duplicado_e_rejeitado(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);
        $usuario = User::factory()->create();
        $usuario->givePermissionTo('irmaos.criar');

        $this->irmao('Irmão Original', '999888');

        $this->actingAs($usuario)->post(route('admin.irmaos.store'), [
            'nome_completo' => 'Irmão Duplicado',
            'cim' => '999888',
            'situacao_cadastral' => SituacaoCadastralIrmao::ATIVO->value,
        ])->assertSessionHasErrors('cim');
    }

    public function test_atualizar_irmao_mantendo_proprio_cim_nao_acusa_duplicidade(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);
        $usuario = User::factory()->create();
        $usuario->givePermissionTo('irmaos.editar');

        $irmao = $this->irmao('Irmão Editado', '777666');

        $this->actingAs($usuario)->put(route('admin.irmaos.update', $irmao), [
            'nome_completo' => 'Irmão Editado com Novo Nome',
            'cim' => '777666',
            'situacao_cadastral' => SituacaoCadastralIrmao::ATIVO->value,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame('Irmão Editado com Novo Nome', $irmao->fresh()->nome_completo);
    }

    // ---------------------------------------------------------------
    // 5 a 7 — sessões históricas e privacidade
    // ---------------------------------------------------------------

    public function test_pode_criar_sessao_historica_de_2025_pela_chancelaria(): void
    {
        $usuario = $this->chanceler();

        $this->actingAs($usuario)->post(route('admin.chancelaria.frequencias.armazenar-sessao'), [
            'inicio_em' => '2025-01-15 20:00',
            'sessao_classe' => ClasseSessao::ORDINARIA->value,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $evento = Evento::query()->where('tipo', TipoEvento::SESSAO->value)->firstOrFail();

        $this->assertSame('2025-01-15', $evento->inicio_em->toDateString());
        $this->assertSame(ClasseSessao::ORDINARIA, $evento->sessao_classe);
    }

    public function test_sessao_criada_pela_chancelaria_e_restrita(): void
    {
        $usuario = $this->chanceler();

        $this->actingAs($usuario)->post(route('admin.chancelaria.frequencias.armazenar-sessao'), [
            'inicio_em' => '2025-02-12 20:00',
        ])->assertRedirect();

        $evento = Evento::query()->where('tipo', TipoEvento::SESSAO->value)->firstOrFail();

        $this->assertSame(VisibilidadeEvento::RESTRITA, $evento->visibilidade);
    }

    public function test_sessao_nunca_aparece_nas_consultas_publicas(): void
    {
        // Mesmo forçada a pública no banco, a consulta pública deve excluí-la.
        $sessao = Evento::factory()->create([
            'titulo' => 'Sessao Secreta da Loja',
            'tipo' => TipoEvento::SESSAO,
            'status' => StatusEvento::PUBLICADO,
            'visibilidade' => VisibilidadeEvento::PUBLICA,
            'inicio_em' => Carbon::parse('2025-03-01 20:00'),
        ]);

        $publico = Evento::query()->publicoNoSite()->pluck('id');

        $this->assertNotContains($sessao->id, $publico->all());
    }

    public function test_evento_publico_normal_continua_visivel(): void
    {
        $evento = Evento::factory()->create([
            'tipo' => TipoEvento::EVENTO,
            'status' => StatusEvento::PUBLICADO,
            'visibilidade' => VisibilidadeEvento::PUBLICA,
            'inicio_em' => Carbon::parse('2025-03-01 20:00'),
        ]);

        $this->assertContains($evento->id, Evento::query()->publicoNoSite()->pluck('id')->all());
    }

    public function test_admin_nao_pode_salvar_sessao_com_visibilidade_publica(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);
        $usuario = User::factory()->create();
        $usuario->givePermissionTo('eventos.criar');

        $this->actingAs($usuario)->post(route('admin.eventos.store'), [
            'titulo' => 'Sessão Indevida',
            'slug' => 'sessao-indevida',
            'tipo' => TipoEvento::SESSAO->value,
            'status' => StatusEvento::PUBLICADO->value,
            'visibilidade' => VisibilidadeEvento::PUBLICA->value,
            'inicio_em' => '2025-04-01 20:00',
        ])->assertSessionHasErrors('visibilidade');
    }

    // ---------------------------------------------------------------
    // 8 a 11 — lançamento de frequência
    // ---------------------------------------------------------------

    public function test_presente_ausente_e_justificado_sao_salvos(): void
    {
        $usuario = $this->chanceler();
        $sessao = $this->sessao('2025-01-15 20:00');

        $presente = $this->irmao('Irmão Presente', '001');
        $ausente = $this->irmao('Irmão Ausente', '002');
        $justificado = $this->irmao('Irmão Justificado', '003');
        $naoInformado = $this->irmao('Irmão Não Informado', '004');

        $this->actingAs($usuario)->put(route('admin.chancelaria.frequencias.update', $sessao), [
            'frequencias' => [
                $presente->id => ['status' => StatusFrequencia::PRESENTE->value],
                $ausente->id => ['status' => StatusFrequencia::AUSENTE->value],
                $justificado->id => ['status' => StatusFrequencia::JUSTIFICADO->value],
                $naoInformado->id => ['status' => ''],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('chancelaria_frequencias', ['evento_id' => $sessao->id, 'irmao_id' => $presente->id, 'status' => 'presente']);
        $this->assertDatabaseHas('chancelaria_frequencias', ['evento_id' => $sessao->id, 'irmao_id' => $ausente->id, 'status' => 'ausente']);
        $this->assertDatabaseHas('chancelaria_frequencias', ['evento_id' => $sessao->id, 'irmao_id' => $justificado->id, 'status' => 'justificado']);
    }

    public function test_nao_informado_nao_vira_ausencia(): void
    {
        $usuario = $this->chanceler();
        $sessao = $this->sessao('2025-01-15 20:00');
        $irmao = $this->irmao('Irmão Sem Lançamento', '010');

        $this->actingAs($usuario)->put(route('admin.chancelaria.frequencias.update', $sessao), [
            'frequencias' => [
                $irmao->id => ['status' => ''],
            ],
        ])->assertRedirect();

        // Nenhum registro é criado — e nada vira "ausente".
        $this->assertDatabaseMissing('chancelaria_frequencias', ['evento_id' => $sessao->id, 'irmao_id' => $irmao->id]);
        $this->assertSame(0, ChancelariaFrequencia::query()->where('status', StatusFrequencia::AUSENTE->value)->count());
    }

    // ---------------------------------------------------------------
    // 12 a 20 — cálculo e relatório
    // ---------------------------------------------------------------

    /**
     * Cenário oficial do MVP: 9 presentes, 8 ausentes, 3 justificadas
     * => 20 consideradas => 45%.
     */
    public function test_calculo_de_frequencia_do_cenario_oficial(): void
    {
        $irmao = $this->irmao('Irmão Referência', '519328');
        $outro = $this->irmao('Irmão Sem Dados', '000');

        $distribuicao = array_merge(
            array_fill(0, 9, StatusFrequencia::PRESENTE),
            array_fill(0, 8, StatusFrequencia::AUSENTE),
            array_fill(0, 3, StatusFrequencia::JUSTIFICADO),
        );

        foreach ($distribuicao as $indice => $status) {
            $sessao = $this->sessao(Carbon::parse('2025-01-01')->addDays($indice * 7)->format('Y-m-d H:i'));
            ChancelariaFrequencia::create([
                'evento_id' => $sessao->id,
                'irmao_id' => $irmao->id,
                'status' => $status,
            ]);
        }

        $resultado = (new CalculadoraFrequencia)->apurar(
            Carbon::parse('2025-01-01')->startOfDay(),
            Carbon::parse('2025-12-31')->endOfDay(),
        );

        $linha = $resultado['linhas']->firstWhere('irmao_id', $irmao->id);

        $this->assertSame(20, $resultado['total_sessoes']);
        $this->assertSame(20, $linha['consideradas']);
        $this->assertSame(9, $linha['presencas']);
        $this->assertSame(8, $linha['ausencias']);
        $this->assertSame(3, $linha['justificadas']);
        $this->assertSame(0, $linha['nao_informadas']);
        $this->assertSame(45.0, $linha['percentual']);
        $this->assertTrue($linha['abaixo_do_limite']);

        // Irmão sem nenhum lançamento: sem percentual, não 0%.
        $semDados = $resultado['linhas']->firstWhere('irmao_id', $outro->id);
        $this->assertSame(0, $semDados['consideradas']);
        $this->assertNull($semDados['percentual']);
        $this->assertFalse($semDados['abaixo_do_limite']);
        $this->assertSame(20, $semDados['nao_informadas']);
    }

    public function test_nao_informado_fica_fora_do_denominador(): void
    {
        $irmao = $this->irmao('Irmão Parcial', '123');

        $comRegistro = $this->sessao('2025-01-15 20:00');
        $this->sessao('2025-02-15 20:00'); // sem lançamento para este Irmão
        $this->sessao('2025-03-15 20:00'); // sem lançamento para este Irmão

        ChancelariaFrequencia::create([
            'evento_id' => $comRegistro->id,
            'irmao_id' => $irmao->id,
            'status' => StatusFrequencia::PRESENTE,
        ]);

        $resultado = (new CalculadoraFrequencia)->apurar(
            Carbon::parse('2025-01-01')->startOfDay(),
            Carbon::parse('2025-12-31')->endOfDay(),
        );
        $linha = $resultado['linhas']->firstWhere('irmao_id', $irmao->id);

        $this->assertSame(3, $resultado['total_sessoes']);
        $this->assertSame(1, $linha['consideradas'], 'Apenas a sessão com lançamento explícito entra no denominador.');
        $this->assertSame(2, $linha['nao_informadas']);
        $this->assertSame(100.0, $linha['percentual']);
    }

    public function test_justificado_conta_apenas_no_denominador(): void
    {
        $irmao = $this->irmao('Irmão Justificado', '456');

        $presenca = $this->sessao('2025-01-15 20:00');
        $justificada = $this->sessao('2025-02-15 20:00');

        ChancelariaFrequencia::create(['evento_id' => $presenca->id, 'irmao_id' => $irmao->id, 'status' => StatusFrequencia::PRESENTE]);
        ChancelariaFrequencia::create(['evento_id' => $justificada->id, 'irmao_id' => $irmao->id, 'status' => StatusFrequencia::JUSTIFICADO]);

        $linha = (new CalculadoraFrequencia)
            ->apurar(Carbon::parse('2025-01-01')->startOfDay(), Carbon::parse('2025-12-31')->endOfDay())['linhas']
            ->firstWhere('irmao_id', $irmao->id);

        $this->assertSame(2, $linha['consideradas']);
        $this->assertSame(1, $linha['justificadas']);
        // Justificada não aumenta a frequência: 1 presença em 2 consideradas.
        $this->assertSame(50.0, $linha['percentual']);
    }

    public function test_relatorio_respeita_o_periodo_informado(): void
    {
        $irmao = $this->irmao('Irmão Periodo', '789');

        $dentro = $this->sessao('2025-06-15 20:00');
        $fora = $this->sessao('2024-06-15 20:00');

        ChancelariaFrequencia::create(['evento_id' => $dentro->id, 'irmao_id' => $irmao->id, 'status' => StatusFrequencia::PRESENTE]);
        ChancelariaFrequencia::create(['evento_id' => $fora->id, 'irmao_id' => $irmao->id, 'status' => StatusFrequencia::AUSENTE]);

        $resultado = (new CalculadoraFrequencia)->apurar(
            Carbon::parse('2025-01-01')->startOfDay(),
            Carbon::parse('2025-12-31')->endOfDay(),
        );
        $linha = $resultado['linhas']->firstWhere('irmao_id', $irmao->id);

        $this->assertSame(1, $resultado['total_sessoes'], 'A sessão de 2024 não pertence ao período.');
        $this->assertSame(1, $linha['consideradas']);
        $this->assertSame(100.0, $linha['percentual']);
    }

    public function test_tela_do_relatorio_exibe_indicador_e_nota_de_criterio(): void
    {
        $usuario = $this->chanceler();
        $irmao = $this->irmao('Irmão Abaixo', '321');

        $sessaoPresente = $this->sessao('2025-01-15 20:00');
        $sessaoAusente = $this->sessao('2025-02-15 20:00');
        $sessaoAusente2 = $this->sessao('2025-03-15 20:00');

        ChancelariaFrequencia::create(['evento_id' => $sessaoPresente->id, 'irmao_id' => $irmao->id, 'status' => StatusFrequencia::PRESENTE]);
        ChancelariaFrequencia::create(['evento_id' => $sessaoAusente->id, 'irmao_id' => $irmao->id, 'status' => StatusFrequencia::AUSENTE]);
        ChancelariaFrequencia::create(['evento_id' => $sessaoAusente2->id, 'irmao_id' => $irmao->id, 'status' => StatusFrequencia::AUSENTE]);

        $resposta = $this->actingAs($usuario)->get(route('admin.chancelaria.relatorios.frequencia', [
            'inicio' => '2025-01-01',
            'fim' => '2025-12-31',
        ]));

        $resposta->assertOk();
        $resposta->assertSee('Irmão Abaixo');
        $resposta->assertSee('321');
        $resposta->assertSee('Frequência abaixo de 50%');
        $resposta->assertSee('Sessões encontradas no período');
        $resposta->assertSee('Critério deste relatório');
        // Não deve emitir julgamento institucional.
        $resposta->assertDontSee('Inapto');
        $resposta->assertDontSee('Inelegível');
        $resposta->assertDontSee('Irregular');
    }

    public function test_relatorio_registra_auditoria(): void
    {
        $usuario = $this->chanceler();

        $this->actingAs($usuario)->get(route('admin.chancelaria.relatorios.frequencia', [
            'inicio' => '2025-01-01',
            'fim' => '2025-12-31',
        ]))->assertOk();

        $this->assertDatabaseHas('auditorias', [
            'modulo' => 'chancelaria',
            'acao' => 'gerar-relatorio-frequencia',
        ]);
    }

    public function test_listagem_de_sessoes_filtra_por_tipo_e_ano(): void
    {
        $usuario = $this->chanceler();

        $sessao2025 = $this->sessao('2025-05-10 20:00');
        $sessao2026 = $this->sessao('2026-05-10 20:00');
        $eventoComum = Evento::factory()->create([
            'titulo' => 'Festa Junina Publica',
            'tipo' => TipoEvento::EVENTO,
            'inicio_em' => Carbon::parse('2025-06-10 20:00'),
        ]);

        $resposta = $this->actingAs($usuario)->get(route('admin.chancelaria.frequencias.selecionar-evento', ['ano' => 2025]));

        $resposta->assertOk();
        $resposta->assertSee($sessao2025->titulo);
        $resposta->assertDontSee($sessao2026->titulo);
        $resposta->assertDontSee('Festa Junina Publica');
        $this->assertNotNull($eventoComum->id);
    }
}
