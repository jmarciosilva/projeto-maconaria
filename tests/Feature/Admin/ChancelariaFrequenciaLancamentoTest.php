<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

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
use Database\Seeders\PerfilPermissaoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Segurança do lançamento de frequência.
 *
 * Um lançamento já afirmado pelo Chanceler (presente, ausente ou
 * justificado) só pode desaparecer por ato deliberado. Salvar o
 * formulário e as ações em massa nunca podem apagá-lo ou sobrescrevê-lo.
 */
class ChancelariaFrequenciaLancamentoTest extends TestCase
{
    use RefreshDatabase;

    private function chanceler(): User
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
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
    // Salvar não apaga
    // ---------------------------------------------------------------

    public function test_salvar_com_campo_vazio_nao_apaga_lancamento_existente(): void
    {
        $usuario = $this->chanceler();
        $evento = $this->sessao();
        $irmao = $this->irmao('Irmão Lançado', '100001');

        ChancelariaFrequencia::create([
            'evento_id' => $evento->id,
            'irmao_id' => $irmao->id,
            'status' => StatusFrequencia::PRESENTE,
            'observacao' => 'chegou no tempo',
        ]);

        $this->actingAs($usuario)
            ->put(route('admin.chancelaria.frequencias.update', $evento), [
                'frequencias' => [$irmao->id => ['status' => '', 'observacao' => '']],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('chancelaria_frequencias', [
            'evento_id' => $evento->id,
            'irmao_id' => $irmao->id,
            'status' => StatusFrequencia::PRESENTE->value,
        ]);
        $this->assertSame(1, ChancelariaFrequencia::query()->count());
    }

    public function test_salvar_com_campo_vazio_nao_cria_lancamento(): void
    {
        $usuario = $this->chanceler();
        $evento = $this->sessao();
        $irmao = $this->irmao('Irmão Sem Lançamento', '100002');

        $this->actingAs($usuario)
            ->put(route('admin.chancelaria.frequencias.update', $evento), [
                'frequencias' => [$irmao->id => ['status' => '', 'observacao' => '']],
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('chancelaria_frequencias', 0);
    }

    public function test_salvar_continua_atualizando_status_informado(): void
    {
        $usuario = $this->chanceler();
        $evento = $this->sessao();
        $irmao = $this->irmao('Irmão Atualizado', '100003');

        ChancelariaFrequencia::create([
            'evento_id' => $evento->id,
            'irmao_id' => $irmao->id,
            'status' => StatusFrequencia::PRESENTE,
        ]);

        $this->actingAs($usuario)
            ->put(route('admin.chancelaria.frequencias.update', $evento), [
                'frequencias' => [$irmao->id => [
                    'status' => StatusFrequencia::JUSTIFICADO->value,
                    'observacao' => 'Emergência médica',
                ]],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('chancelaria_frequencias', [
            'evento_id' => $evento->id,
            'irmao_id' => $irmao->id,
            'status' => StatusFrequencia::JUSTIFICADO->value,
            'observacao' => 'Emergência médica',
        ]);
    }

    // ---------------------------------------------------------------
    // Limpar lançamento
    // ---------------------------------------------------------------

    public function test_limpar_lancamento_remove_o_registro(): void
    {
        $usuario = $this->chanceler();
        $evento = $this->sessao();
        $irmao = $this->irmao('Irmão Removido', '100004');

        ChancelariaFrequencia::create([
            'evento_id' => $evento->id,
            'irmao_id' => $irmao->id,
            'status' => StatusFrequencia::AUSENTE,
        ]);

        $this->actingAs($usuario)
            ->delete(route('admin.chancelaria.frequencias.limpar', [$evento, $irmao]))
            ->assertRedirect();

        $this->assertDatabaseCount('chancelaria_frequencias', 0);
    }

    public function test_limpar_lancamento_registra_auditoria_com_o_valor_anterior(): void
    {
        $usuario = $this->chanceler();
        $evento = $this->sessao();
        $irmao = $this->irmao('Irmão Auditado', '100005');

        ChancelariaFrequencia::create([
            'evento_id' => $evento->id,
            'irmao_id' => $irmao->id,
            'status' => StatusFrequencia::JUSTIFICADO,
            'observacao' => 'Compromisso profissional',
        ]);

        $this->actingAs($usuario)
            ->delete(route('admin.chancelaria.frequencias.limpar', [$evento, $irmao]))
            ->assertRedirect();

        $this->assertDatabaseHas('auditorias', [
            'modulo' => 'chancelaria',
            'acao' => 'limpar-frequencia',
            'entidade' => 'Evento',
            'entidade_id' => $evento->id,
        ]);

        $auditoria = Auditoria::query()->where('acao', 'limpar-frequencia')->firstOrFail();

        $this->assertSame(StatusFrequencia::JUSTIFICADO->value, $auditoria->dados_anteriores['status']);
        $this->assertSame('Compromisso profissional', $auditoria->dados_anteriores['observacao']);
        $this->assertSame($irmao->id, $auditoria->dados_anteriores['irmao_id']);
    }

    public function test_limpar_lancamento_exige_permissao_de_edicao(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);
        $evento = $this->sessao();
        $irmao = $this->irmao('Irmão Protegido', '100006');

        ChancelariaFrequencia::create([
            'evento_id' => $evento->id,
            'irmao_id' => $irmao->id,
            'status' => StatusFrequencia::PRESENTE,
        ]);

        $semPermissao = User::factory()->create();
        $semPermissao->givePermissionTo('chancelaria.visualizar');

        $this->actingAs($semPermissao)
            ->delete(route('admin.chancelaria.frequencias.limpar', [$evento, $irmao]))
            ->assertForbidden();

        $this->assertDatabaseCount('chancelaria_frequencias', 1);
    }

    public function test_limpar_lancamento_exige_autenticacao(): void
    {
        $evento = $this->sessao();
        $irmao = $this->irmao('Irmão Anônimo', '100007');

        $this->delete(route('admin.chancelaria.frequencias.limpar', [$evento, $irmao]))
            ->assertRedirect(route('login'));
    }

    public function test_limpar_lancamento_inexistente_avisa_sem_erro(): void
    {
        $usuario = $this->chanceler();
        $evento = $this->sessao();
        $irmao = $this->irmao('Irmão Sem Nada', '100008');

        $this->actingAs($usuario)
            ->delete(route('admin.chancelaria.frequencias.limpar', [$evento, $irmao]))
            ->assertRedirect()
            ->assertSessionHas('erro');
    }

    // ---------------------------------------------------------------
    // Ações em massa — somente pendentes
    // ---------------------------------------------------------------

    public function test_acoes_em_massa_so_alteram_selects_sem_lancamento(): void
    {
        $conteudo = (string) file_get_contents(
            resource_path('views/admin/chancelaria/frequencias/edit.blade.php'),
        );

        // A guarda é client-side (Alpine): só entra no if quem está vazio.
        $this->assertStringContainsString("if (s.value === '')", $conteudo);
        $this->assertStringContainsString('marcarPendentes', $conteudo);
        $this->assertStringNotContainsString('marcarTodos', $conteudo);
    }

    public function test_botoes_em_massa_falam_de_pendentes(): void
    {
        $usuario = $this->chanceler();
        $evento = $this->sessao();
        $this->irmao('Irmão da Tela', '100009');

        $resposta = $this->actingAs($usuario)->get(route('admin.chancelaria.frequencias.edit', $evento));

        $resposta->assertOk();
        $resposta->assertSee('Marcar pendentes como presentes');
        $resposta->assertSee('Marcar pendentes como ausentes');
        $resposta->assertDontSee('Marcar todos como Presente');
        $resposta->assertDontSee('Marcar todos como Ausente');
        $resposta->assertSee('As ações em massa alteram apenas Irmãos sem lançamento.');
    }

    /**
     * O servidor é a garantia final: mesmo que alguém envie um POST
     * forjado, um status já lançado só muda para outro status explícito.
     */
    public function test_status_ja_lancado_nao_e_sobrescrito_por_envio_vazio(): void
    {
        $usuario = $this->chanceler();
        $evento = $this->sessao();

        $presente = $this->irmao('Irmão Presente', '100010');
        $ausente = $this->irmao('Irmão Ausente', '100011');
        $justificado = $this->irmao('Irmão Justificado', '100012');

        foreach ([
            [$presente, StatusFrequencia::PRESENTE],
            [$ausente, StatusFrequencia::AUSENTE],
            [$justificado, StatusFrequencia::JUSTIFICADO],
        ] as [$irmao, $status]) {
            ChancelariaFrequencia::create([
                'evento_id' => $evento->id,
                'irmao_id' => $irmao->id,
                'status' => $status,
            ]);
        }

        $this->actingAs($usuario)
            ->put(route('admin.chancelaria.frequencias.update', $evento), [
                'frequencias' => [
                    $presente->id => ['status' => ''],
                    $ausente->id => ['status' => ''],
                    $justificado->id => ['status' => ''],
                ],
            ])
            ->assertRedirect();

        $this->assertSame(StatusFrequencia::PRESENTE, ChancelariaFrequencia::query()
            ->where('irmao_id', $presente->id)->firstOrFail()->status);
        $this->assertSame(StatusFrequencia::AUSENTE, ChancelariaFrequencia::query()
            ->where('irmao_id', $ausente->id)->firstOrFail()->status);
        $this->assertSame(StatusFrequencia::JUSTIFICADO, ChancelariaFrequencia::query()
            ->where('irmao_id', $justificado->id)->firstOrFail()->status);
    }

    // ---------------------------------------------------------------
    // Motivo da justificativa
    // ---------------------------------------------------------------

    public function test_motivo_existente_reaparece_na_edicao(): void
    {
        $usuario = $this->chanceler();
        $evento = $this->sessao();
        $irmao = $this->irmao('Irmão Justificado', '100013');

        ChancelariaFrequencia::create([
            'evento_id' => $evento->id,
            'irmao_id' => $irmao->id,
            'status' => StatusFrequencia::JUSTIFICADO,
            'observacao' => 'Emergência médica na família',
        ]);

        $resposta = $this->actingAs($usuario)->get(route('admin.chancelaria.frequencias.edit', $evento));

        $resposta->assertOk();
        $resposta->assertSee('Emergência médica na família', false);
    }

    public function test_tela_apresenta_o_campo_como_motivo_da_justificativa(): void
    {
        $usuario = $this->chanceler();
        $evento = $this->sessao();
        $this->irmao('Irmão Qualquer', '100014');

        $resposta = $this->actingAs($usuario)->get(route('admin.chancelaria.frequencias.edit', $evento));

        $resposta->assertOk();
        $resposta->assertSee('Motivo da justificativa');
        $resposta->assertSee('exigido para concluir a frequência');
    }

    public function test_opcao_vazia_some_depois_de_lancado(): void
    {
        $usuario = $this->chanceler();
        $evento = $this->sessao();
        $comLancamento = $this->irmao('Irmão Com Lançamento', '100015');
        $this->irmao('Irmão Pendente', '100016');

        ChancelariaFrequencia::create([
            'evento_id' => $evento->id,
            'irmao_id' => $comLancamento->id,
            'status' => StatusFrequencia::PRESENTE,
        ]);

        $conteudo = (string) $this->actingAs($usuario)
            ->get(route('admin.chancelaria.frequencias.edit', $evento))
            ->assertOk()
            ->getContent();

        // Um único select ainda oferece "sem lançamento": o do Irmão pendente.
        $this->assertSame(1, substr_count($conteudo, 'sem lançamento &mdash;'));
        $this->assertStringContainsString('Limpar lançamento', $conteudo);
    }

    // ---------------------------------------------------------------
    // Nada de regra de cálculo mudou
    // ---------------------------------------------------------------

    public function test_apuracao_continua_identica(): void
    {
        $evento = $this->sessao('2025-01-15 20:00');
        $this->sessao('2025-02-15 20:00');
        $irmao = $this->irmao('Irmão Contrato', '100999');

        ChancelariaFrequencia::create([
            'evento_id' => $evento->id,
            'irmao_id' => $irmao->id,
            'status' => StatusFrequencia::PRESENTE,
        ]);

        $linha = (new CalculadoraFrequencia)
            ->apurar(Carbon::parse('2025-01-01')->startOfDay(), Carbon::parse('2025-12-31')->endOfDay())['linhas']
            ->firstWhere('irmao_id', $irmao->id);

        $this->assertSame(1, $linha['consideradas']);
        $this->assertSame(1, $linha['nao_informadas']);
        $this->assertSame(100.0, $linha['percentual']);
    }
}
