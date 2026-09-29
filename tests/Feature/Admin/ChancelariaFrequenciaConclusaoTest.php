<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\AbrangenciaFrequencia;
use App\Enums\SituacaoCadastralIrmao;
use App\Enums\StatusEvento;
use App\Enums\StatusFrequencia;
use App\Enums\TipoEvento;
use App\Enums\VisibilidadeEvento;
use App\Models\Auditoria;
use App\Models\ChancelariaFrequencia;
use App\Models\Evento;
use App\Models\Irmao;
use App\Models\User;
use App\Support\Chancelaria\CalculadoraFrequencia;
use App\Support\Chancelaria\ConclusaoDeFrequencia;
use Database\Seeders\PerfilPermissaoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Conclusão e reabertura da frequência de uma sessão.
 *
 * Concluir é o Chanceler afirmando que terminou o lançamento. A conclusão
 * apenas valida e bloqueia: nunca inventa presença, ausência ou motivo.
 */
class ChancelariaFrequenciaConclusaoTest extends TestCase
{
    use RefreshDatabase;

    private function chanceler(): User
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create(['name' => 'Chanceler de Teste']);
        $usuario->givePermissionTo('chancelaria.visualizar', 'chancelaria.criar', 'chancelaria.editar');

        return $usuario;
    }

    private function sessao(string $data = '2025-05-10 20:00'): Evento
    {
        return Evento::factory()->create([
            'tipo' => TipoEvento::SESSAO,
            'status' => StatusEvento::PUBLICADO,
            'visibilidade' => VisibilidadeEvento::RESTRITA,
            'inicio_em' => Carbon::parse($data),
        ]);
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    private function irmao(string $nome, array $extra = []): Irmao
    {
        return Irmao::factory()->create(array_merge([
            'nome_completo' => $nome,
            'cpf' => null,
            'situacao_cadastral' => SituacaoCadastralIrmao::ATIVO,
            'data_ingresso_loja' => '2020-01-01',
        ], $extra));
    }

    private function lancar(Evento $evento, Irmao $irmao, StatusFrequencia $status, ?string $observacao = null): void
    {
        ChancelariaFrequencia::create([
            'evento_id' => $evento->id,
            'irmao_id' => $irmao->id,
            'status' => $status,
            'observacao' => $observacao,
        ]);
    }

    // ---------------------------------------------------------------
    // Regra de abrangência
    // ---------------------------------------------------------------

    public function test_irmao_que_ingressou_depois_da_sessao_nao_e_abrangido(): void
    {
        $evento = $this->sessao('2025-05-10 20:00');
        $irmao = $this->irmao('Irmão Novo', ['data_ingresso_loja' => '2025-08-01']);

        $this->assertSame(
            AbrangenciaFrequencia::NAO_ABRANGIDO,
            ConclusaoDeFrequencia::abrangencia($irmao, $evento->inicio_em),
        );
    }

    public function test_irmao_desligado_antes_da_sessao_nao_e_abrangido(): void
    {
        $evento = $this->sessao('2025-05-10 20:00');
        $irmao = $this->irmao('Irmão Desligado', [
            'data_ingresso_loja' => '2018-01-01',
            'data_desligamento' => '2024-12-31',
        ]);

        $this->assertSame(
            AbrangenciaFrequencia::NAO_ABRANGIDO,
            ConclusaoDeFrequencia::abrangencia($irmao, $evento->inicio_em),
        );
    }

    public function test_desligamento_na_propria_data_da_sessao_ainda_abrange(): void
    {
        $evento = $this->sessao('2025-05-10 20:00');
        $irmao = $this->irmao('Irmão Limite', [
            'data_ingresso_loja' => '2018-01-01',
            'data_desligamento' => '2025-05-10',
        ]);

        $this->assertSame(
            AbrangenciaFrequencia::ABRANGIDO,
            ConclusaoDeFrequencia::abrangencia($irmao, $evento->inicio_em),
        );
    }

    public function test_ingresso_na_propria_data_da_sessao_abrange(): void
    {
        $evento = $this->sessao('2025-05-10 20:00');
        $irmao = $this->irmao('Irmão Recem', ['data_ingresso_loja' => '2025-05-10']);

        $this->assertSame(
            AbrangenciaFrequencia::ABRANGIDO,
            ConclusaoDeFrequencia::abrangencia($irmao, $evento->inicio_em),
        );
    }

    public function test_data_de_iniciacao_supre_a_falta_de_data_de_ingresso(): void
    {
        $evento = $this->sessao('2025-05-10 20:00');
        $irmao = $this->irmao('Irmão Iniciado', [
            'data_ingresso_loja' => null,
            'data_iniciacao' => '2019-03-15',
        ]);

        $this->assertSame(
            AbrangenciaFrequencia::ABRANGIDO,
            ConclusaoDeFrequencia::abrangencia($irmao, $evento->inicio_em),
        );
    }

    public function test_sem_nenhuma_data_a_participacao_e_indeterminada(): void
    {
        $evento = $this->sessao('2025-05-10 20:00');
        $irmao = $this->irmao('Irmão Sem Datas', [
            'data_ingresso_loja' => null,
            'data_iniciacao' => null,
        ]);

        $this->assertSame(
            AbrangenciaFrequencia::INDETERMINADO,
            ConclusaoDeFrequencia::abrangencia($irmao, $evento->inicio_em),
        );
    }

    public function test_situacao_cadastral_atual_nao_altera_abrangencia_historica(): void
    {
        $evento = $this->sessao('2025-05-10 20:00');

        // Inativo hoje, mas estava na Loja na data da sessão: o estado atual
        // não pode reescrever o passado.
        $irmao = $this->irmao('Irmão Inativo Hoje', [
            'data_ingresso_loja' => '2018-01-01',
            'situacao_cadastral' => SituacaoCadastralIrmao::INATIVO,
        ]);

        $this->assertSame(
            AbrangenciaFrequencia::ABRANGIDO,
            ConclusaoDeFrequencia::abrangencia($irmao, $evento->inicio_em),
        );
    }

    // ---------------------------------------------------------------
    // Pendências
    // ---------------------------------------------------------------

    public function test_ingresso_posterior_a_sessao_nao_gera_pendencia(): void
    {
        $evento = $this->sessao('2025-05-10 20:00');
        $this->irmao('Irmão Futuro', ['data_ingresso_loja' => '2026-01-01']);

        $pendencias = ConclusaoDeFrequencia::pendencias($evento);

        $this->assertCount(0, $pendencias['sem_lancamento']);
        $this->assertCount(0, $pendencias['indeterminados']);
        $this->assertFalse(ConclusaoDeFrequencia::possuiPendencias($pendencias));
    }

    public function test_desligamento_anterior_a_sessao_nao_gera_pendencia(): void
    {
        $evento = $this->sessao('2025-05-10 20:00');
        $this->irmao('Irmão Saiu', [
            'data_ingresso_loja' => '2015-01-01',
            'data_desligamento' => '2024-01-01',
        ]);

        $this->assertFalse(ConclusaoDeFrequencia::possuiPendencias(ConclusaoDeFrequencia::pendencias($evento)));
    }

    public function test_lancamento_explicito_resolve_participacao_indeterminada(): void
    {
        $evento = $this->sessao('2025-05-10 20:00');
        $irmao = $this->irmao('Irmão Sem Datas', [
            'data_ingresso_loja' => null,
            'data_iniciacao' => null,
        ]);

        $antes = ConclusaoDeFrequencia::pendencias($evento);
        $this->assertCount(1, $antes['indeterminados']);

        $this->lancar($evento, $irmao, StatusFrequencia::PRESENTE);

        $depois = ConclusaoDeFrequencia::pendencias($evento);
        $this->assertCount(0, $depois['indeterminados']);
        $this->assertFalse(ConclusaoDeFrequencia::possuiPendencias($depois));
    }

    // ---------------------------------------------------------------
    // Concluir
    // ---------------------------------------------------------------

    public function test_conclusao_recusa_abrangido_sem_lancamento(): void
    {
        $usuario = $this->chanceler();
        $evento = $this->sessao();
        $this->irmao('Irmão Pendente');

        $this->actingAs($usuario)
            ->post(route('admin.chancelaria.frequencias.concluir', $evento))
            ->assertRedirect()
            ->assertSessionHas('erro');

        $this->assertNull($evento->fresh()->frequencia_concluida_em);
    }

    public function test_conclusao_recusa_justificado_sem_motivo(): void
    {
        $usuario = $this->chanceler();
        $evento = $this->sessao();
        $irmao = $this->irmao('Irmão Justificado');

        $this->lancar($evento, $irmao, StatusFrequencia::JUSTIFICADO, null);

        $resposta = $this->actingAs($usuario)
            ->post(route('admin.chancelaria.frequencias.concluir', $evento))
            ->assertRedirect();

        $this->assertStringContainsString('sem motivo informado', (string) session('erro'));
        $this->assertNull($evento->fresh()->frequencia_concluida_em);
    }

    public function test_conclusao_recusa_participacao_indeterminada(): void
    {
        $usuario = $this->chanceler();
        $evento = $this->sessao();
        $this->irmao('Irmão Sem Datas', ['data_ingresso_loja' => null, 'data_iniciacao' => null]);

        $this->actingAs($usuario)
            ->post(route('admin.chancelaria.frequencias.concluir', $evento))
            ->assertRedirect();

        $this->assertStringContainsString('sem data de ingresso', (string) session('erro'));
        $this->assertNull($evento->fresh()->frequencia_concluida_em);
    }

    public function test_conclusao_nao_preenche_nenhum_lancamento_automaticamente(): void
    {
        $usuario = $this->chanceler();
        $evento = $this->sessao();
        $this->irmao('Irmão Pendente');

        $this->actingAs($usuario)->post(route('admin.chancelaria.frequencias.concluir', $evento));

        $this->assertDatabaseCount('chancelaria_frequencias', 0);
    }

    public function test_conclusao_bem_sucedida_marca_quem_e_quando(): void
    {
        $usuario = $this->chanceler();
        $evento = $this->sessao();
        $irmao = $this->irmao('Irmão Presente');

        $this->lancar($evento, $irmao, StatusFrequencia::PRESENTE);

        $this->actingAs($usuario)
            ->post(route('admin.chancelaria.frequencias.concluir', $evento))
            ->assertRedirect()
            ->assertSessionHas('sucesso');

        $evento->refresh();

        $this->assertNotNull($evento->frequencia_concluida_em);
        $this->assertSame($usuario->id, $evento->frequencia_concluida_por_id);
        $this->assertTrue($evento->frequenciaConcluida());
    }

    public function test_conclusao_aceita_justificado_com_motivo(): void
    {
        $usuario = $this->chanceler();
        $evento = $this->sessao();
        $irmao = $this->irmao('Irmão Justificado');

        $this->lancar($evento, $irmao, StatusFrequencia::JUSTIFICADO, 'Emergência médica');

        $this->actingAs($usuario)
            ->post(route('admin.chancelaria.frequencias.concluir', $evento))
            ->assertSessionHas('sucesso');

        $this->assertNotNull($evento->fresh()->frequencia_concluida_em);
    }

    public function test_conclusao_e_auditada(): void
    {
        $usuario = $this->chanceler();
        $evento = $this->sessao();
        $this->lancar($evento, $this->irmao('Irmão Presente'), StatusFrequencia::PRESENTE);

        $this->actingAs($usuario)->post(route('admin.chancelaria.frequencias.concluir', $evento));

        $this->assertDatabaseHas('auditorias', [
            'modulo' => 'chancelaria',
            'acao' => 'concluir-frequencia',
            'entidade' => 'Evento',
            'entidade_id' => $evento->id,
        ]);
    }

    public function test_conclusao_exige_permissao_de_edicao(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);
        $evento = $this->sessao();
        $this->lancar($evento, $this->irmao('Irmão Presente'), StatusFrequencia::PRESENTE);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('chancelaria.visualizar');

        $this->actingAs($usuario)
            ->post(route('admin.chancelaria.frequencias.concluir', $evento))
            ->assertForbidden();

        $this->assertNull($evento->fresh()->frequencia_concluida_em);
    }

    // ---------------------------------------------------------------
    // Sessão concluída é somente leitura
    // ---------------------------------------------------------------

    public function test_sessao_concluida_recusa_update_direto(): void
    {
        $usuario = $this->chanceler();
        $evento = $this->sessao();
        $irmao = $this->irmao('Irmão Presente');

        $this->lancar($evento, $irmao, StatusFrequencia::PRESENTE);
        $this->actingAs($usuario)->post(route('admin.chancelaria.frequencias.concluir', $evento));

        $this->actingAs($usuario)
            ->put(route('admin.chancelaria.frequencias.update', $evento), [
                'frequencias' => [$irmao->id => ['status' => StatusFrequencia::AUSENTE->value]],
            ])
            ->assertRedirect()
            ->assertSessionHas('erro');

        $this->assertSame(
            StatusFrequencia::PRESENTE,
            ChancelariaFrequencia::query()->where('irmao_id', $irmao->id)->firstOrFail()->status,
        );
    }

    public function test_sessao_concluida_recusa_limpar_lancamento(): void
    {
        $usuario = $this->chanceler();
        $evento = $this->sessao();
        $irmao = $this->irmao('Irmão Presente');

        $this->lancar($evento, $irmao, StatusFrequencia::PRESENTE);
        $this->actingAs($usuario)->post(route('admin.chancelaria.frequencias.concluir', $evento));

        $this->actingAs($usuario)
            ->delete(route('admin.chancelaria.frequencias.limpar', [$evento, $irmao]))
            ->assertRedirect()
            ->assertSessionHas('erro');

        $this->assertDatabaseCount('chancelaria_frequencias', 1);
    }

    public function test_tela_de_sessao_concluida_fica_em_somente_leitura(): void
    {
        $usuario = $this->chanceler();
        $evento = $this->sessao();
        $irmao = $this->irmao('Irmão Presente');
        $this->lancar($evento, $irmao, StatusFrequencia::PRESENTE);
        $this->actingAs($usuario)->post(route('admin.chancelaria.frequencias.concluir', $evento));

        $resposta = $this->actingAs($usuario)->get(route('admin.chancelaria.frequencias.edit', $evento));
        $conteudo = (string) $resposta->getContent();

        $resposta->assertOk();
        $resposta->assertSee('Frequência concluída');
        $resposta->assertSee('somente leitura');
        $resposta->assertSee('Reabrir frequência');
        $resposta->assertSee(e(route('admin.chancelaria.frequencias.reabrir', $evento)), false);

        // Os controles de escrita somem. A verificação é pelas rotas e pelos
        // atributos, porque o modal de ajuda descreve as ações em texto e
        // continua presente — corretamente — mesmo na sessão concluída.
        // @js escapa as barras da URL; comparamos na forma que vai ao HTML.
        $this->assertStringNotContainsString(
            str_replace('/', '\\/', route('admin.chancelaria.frequencias.limpar', [$evento, $irmao])),
            $conteudo,
            'Sessão concluída não pode oferecer limpeza de lançamento.',
        );
        $this->assertStringNotContainsString("marcarPendentes('presente')", $conteudo);
        $this->assertStringNotContainsString("marcarPendentes('ausente')", $conteudo);
        $this->assertStringNotContainsString(
            route('admin.chancelaria.frequencias.concluir', $evento),
            $conteudo,
            'Sessão já concluída não pode oferecer o botão de concluir.',
        );
        // Os selects ficam desabilitados.
        $this->assertStringContainsString('disabled', $conteudo);
    }

    public function test_tela_de_sessao_pendente_oferece_os_controles_de_escrita(): void
    {
        $usuario = $this->chanceler();
        $evento = $this->sessao();
        $irmao = $this->irmao('Irmão Presente');
        $this->lancar($evento, $irmao, StatusFrequencia::PRESENTE);

        $resposta = $this->actingAs($usuario)->get(route('admin.chancelaria.frequencias.edit', $evento));
        $conteudo = (string) $resposta->getContent();

        $resposta->assertOk();
        $resposta->assertSee('Frequência pendente');
        $this->assertStringContainsString(
            route('admin.chancelaria.frequencias.concluir', $evento),
            $conteudo,
        );
        $this->assertStringContainsString(
            str_replace('/', '\\/', route('admin.chancelaria.frequencias.limpar', [$evento, $irmao])),
            $conteudo,
        );
        $this->assertStringContainsString("marcarPendentes('presente')", $conteudo);
        $this->assertStringContainsString("marcarPendentes('ausente')", $conteudo);
    }

    // ---------------------------------------------------------------
    // Reabrir
    // ---------------------------------------------------------------

    public function test_reabertura_restaura_a_possibilidade_de_edicao(): void
    {
        $usuario = $this->chanceler();
        $evento = $this->sessao();
        $irmao = $this->irmao('Irmão Presente');

        $this->lancar($evento, $irmao, StatusFrequencia::PRESENTE);
        $this->actingAs($usuario)->post(route('admin.chancelaria.frequencias.concluir', $evento));

        $this->actingAs($usuario)
            ->post(route('admin.chancelaria.frequencias.reabrir', $evento))
            ->assertRedirect()
            ->assertSessionHas('sucesso');

        $evento->refresh();
        $this->assertNull($evento->frequencia_concluida_em);
        $this->assertNull($evento->frequencia_concluida_por_id);
        $this->assertFalse($evento->frequenciaConcluida());

        // E o update volta a funcionar.
        $this->actingAs($usuario)
            ->put(route('admin.chancelaria.frequencias.update', $evento), [
                'frequencias' => [$irmao->id => ['status' => StatusFrequencia::AUSENTE->value]],
            ])
            ->assertSessionHas('sucesso');

        $this->assertSame(
            StatusFrequencia::AUSENTE,
            ChancelariaFrequencia::query()->where('irmao_id', $irmao->id)->firstOrFail()->status,
        );
    }

    public function test_reabertura_e_auditada_com_os_valores_anteriores(): void
    {
        $usuario = $this->chanceler();
        $evento = $this->sessao();
        $this->lancar($evento, $this->irmao('Irmão Presente'), StatusFrequencia::PRESENTE);
        $this->actingAs($usuario)->post(route('admin.chancelaria.frequencias.concluir', $evento));

        $this->actingAs($usuario)->post(route('admin.chancelaria.frequencias.reabrir', $evento));

        $auditoria = Auditoria::query()->where('acao', 'reabrir-frequencia')->firstOrFail();

        $this->assertSame('chancelaria', $auditoria->modulo);
        $this->assertSame($evento->id, $auditoria->entidade_id);
        $this->assertSame($usuario->id, $auditoria->dados_anteriores['frequencia_concluida_por_id']);
        $this->assertNotNull($auditoria->dados_anteriores['frequencia_concluida_em']);
    }

    public function test_reabrir_sessao_pendente_e_recusado(): void
    {
        $usuario = $this->chanceler();
        $evento = $this->sessao();

        $this->actingAs($usuario)
            ->post(route('admin.chancelaria.frequencias.reabrir', $evento))
            ->assertRedirect()
            ->assertSessionHas('erro');
    }

    public function test_concluir_sessao_ja_concluida_e_recusado(): void
    {
        $usuario = $this->chanceler();
        $evento = $this->sessao();
        $this->lancar($evento, $this->irmao('Irmão Presente'), StatusFrequencia::PRESENTE);
        $this->actingAs($usuario)->post(route('admin.chancelaria.frequencias.concluir', $evento));

        $this->actingAs($usuario)
            ->post(route('admin.chancelaria.frequencias.concluir', $evento))
            ->assertRedirect()
            ->assertSessionHas('erro');
    }

    public function test_reabertura_exige_permissao_de_edicao(): void
    {
        $chanceler = $this->chanceler();
        $evento = $this->sessao();
        $this->lancar($evento, $this->irmao('Irmão Presente'), StatusFrequencia::PRESENTE);
        $this->actingAs($chanceler)->post(route('admin.chancelaria.frequencias.concluir', $evento));

        $semPermissao = User::factory()->create();
        $semPermissao->givePermissionTo('chancelaria.visualizar');

        $this->actingAs($semPermissao)
            ->post(route('admin.chancelaria.frequencias.reabrir', $evento))
            ->assertForbidden();

        $this->assertNotNull($evento->fresh()->frequencia_concluida_em);
    }

    // ---------------------------------------------------------------
    // Nada mais mudou
    // ---------------------------------------------------------------

    public function test_sessoes_existentes_nascem_pendentes(): void
    {
        $evento = $this->sessao();

        $this->assertNull($evento->frequencia_concluida_em);
        $this->assertNull($evento->frequencia_concluida_por_id);
        $this->assertFalse($evento->frequenciaConcluida());
    }

    public function test_calculadora_de_frequencia_permanece_identica(): void
    {
        $usuario = $this->chanceler();

        $comLancamento = $this->sessao('2025-01-15 20:00');
        $this->sessao('2025-02-15 20:00');
        $irmao = $this->irmao('Irmão Contrato');

        $this->lancar($comLancamento, $irmao, StatusFrequencia::PRESENTE);

        $apurar = fn (): array => (new CalculadoraFrequencia)->apurar(
            Carbon::parse('2025-01-01')->startOfDay(),
            Carbon::parse('2025-12-31')->endOfDay(),
        );

        $antes = $apurar();

        // Concluir uma sessão não pode mexer em nenhum número do relatório.
        $this->actingAs($usuario)->post(route('admin.chancelaria.frequencias.concluir', $comLancamento));

        $depois = $apurar();

        $this->assertSame($antes['total_sessoes'], $depois['total_sessoes']);
        $this->assertEquals($antes['linhas']->toArray(), $depois['linhas']->toArray());

        $linha = $depois['linhas']->firstWhere('irmao_id', $irmao->id);
        $this->assertSame(1, $linha['consideradas']);
        $this->assertSame(1, $linha['nao_informadas']);
        $this->assertSame(100.0, $linha['percentual']);
    }
}
