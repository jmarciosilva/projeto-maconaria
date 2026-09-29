<?php

namespace Tests\Feature\Admin;

use App\Enums\ClasseSessao;
use App\Enums\StatusEvento;
use App\Enums\StatusFrequencia;
use App\Enums\TipoEvento;
use App\Enums\VisibilidadeEvento;
use App\Models\ChancelariaFrequencia;
use App\Models\Evento;
use App\Models\Irmao;
use App\Models\User;
use Database\Seeders\PerfilPermissaoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Dashboard da Chancelaria reorganizado em torno do fluxo
 * Sessões → Frequência → Relatório.
 */
class ChancelariaDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function chanceler(): User
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->assignRole('Chanceler');

        return $usuario;
    }

    private function sessao(string $titulo, string $data = '2025-09-17 20:00'): Evento
    {
        return Evento::factory()->create([
            'titulo' => $titulo,
            'tipo' => TipoEvento::SESSAO,
            'status' => StatusEvento::PUBLICADO,
            'visibilidade' => VisibilidadeEvento::RESTRITA,
            'sessao_classe' => ClasseSessao::ORDINARIA,
            'inicio_em' => Carbon::parse($data),
        ]);
    }

    public function test_dashboard_abre_para_chanceler_autorizado(): void
    {
        $this->actingAs($this->chanceler())
            ->get(route('admin.chancelaria.index'))
            ->assertOk()
            ->assertSee('Gestão das sessões, presenças e frequência dos Irmãos');
    }

    public function test_dashboard_apresenta_as_tres_acoes_principais(): void
    {
        $resposta = $this->actingAs($this->chanceler())->get(route('admin.chancelaria.index'));

        $resposta->assertOk();
        // Nova sessão (o Chanceler tem chancelaria.criar)
        $resposta->assertSee(route('admin.chancelaria.sessoes.create'), false);
        $resposta->assertSee('Nova sessão');
        // Registrar frequência
        $resposta->assertSee(route('admin.chancelaria.frequencias.selecionar-evento'), false);
        $resposta->assertSee('Registrar frequência');
        // Relatório de frequência
        $resposta->assertSee(route('admin.chancelaria.relatorios.frequencia'), false);
        $resposta->assertSee('Relatório de frequência');
    }

    public function test_dashboard_da_acesso_a_listagem_de_sessoes(): void
    {
        $this->actingAs($this->chanceler())
            ->get(route('admin.chancelaria.index'))
            ->assertOk()
            ->assertSee(route('admin.chancelaria.sessoes.index'), false)
            ->assertSee('Ver todas');
    }

    public function test_nova_sessao_nao_aparece_sem_permissao_de_criar(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);
        $usuario = User::factory()->create();
        $usuario->givePermissionTo('chancelaria.visualizar');

        $this->actingAs($usuario)
            ->get(route('admin.chancelaria.index'))
            ->assertOk()
            ->assertDontSee(route('admin.chancelaria.sessoes.create'), false);
    }

    public function test_sessoes_recentes_listam_somente_sessoes(): void
    {
        $sessao = $this->sessao('Sessao Ordinaria de Setembro');

        Evento::factory()->create([
            'titulo' => 'Palestra publica sobre historia',
            'tipo' => TipoEvento::EVENTO,
            'status' => StatusEvento::PUBLICADO,
            'visibilidade' => VisibilidadeEvento::PUBLICA,
            'inicio_em' => Carbon::parse('2025-09-20 20:00'),
        ]);

        $resposta = $this->actingAs($this->chanceler())->get(route('admin.chancelaria.index'));

        $resposta->assertOk();
        $resposta->assertSee($sessao->titulo);
        $resposta->assertDontSee('Palestra publica sobre historia');
    }

    public function test_situacao_da_frequencia_e_apresentada_corretamente(): void
    {
        $comLancamento = $this->sessao('Sessao Com Lancamento', '2025-09-17 20:00');
        $this->sessao('Sessao Sem Lancamento', '2025-09-10 20:00');

        $irmao = Irmao::factory()->create(['cpf' => null, 'cim' => '123456']);
        ChancelariaFrequencia::create([
            'evento_id' => $comLancamento->id,
            'irmao_id' => $irmao->id,
            'status' => StatusFrequencia::PRESENTE,
        ]);

        $resposta = $this->actingAs($this->chanceler())->get(route('admin.chancelaria.index'));

        $resposta->assertOk();
        $resposta->assertSee('Frequência registrada');
        $resposta->assertSee('Frequência pendente');
    }

    public function test_resumo_informa_o_periodo_explicitamente(): void
    {
        $this->actingAs($this->chanceler())
            ->get(route('admin.chancelaria.index'))
            ->assertOk()
            ->assertSee('Resumo dos últimos 3 meses')
            ->assertSee('Sessões')
            ->assertSee('Presenças')
            ->assertSee('Ausências')
            ->assertSee('Justificativas');
    }

    public function test_funcoes_secundarias_continuam_acessiveis(): void
    {
        $resposta = $this->actingAs($this->chanceler())->get(route('admin.chancelaria.index'));

        $resposta->assertOk();
        $resposta->assertSee('Outras funções');
        $resposta->assertSee(route('admin.chancelaria.visitantes.index'), false);
        $resposta->assertSee(route('admin.chancelaria.comunicados.index'), false);
    }

    public function test_dashboard_nao_exibe_mais_confirmacoes_nem_visitantes_recentes(): void
    {
        $this->sessao('Sessao Qualquer');

        $resposta = $this->actingAs($this->chanceler())->get(route('admin.chancelaria.index'));

        $resposta->assertOk();
        $resposta->assertDontSee('Eventos recentes');
        $resposta->assertDontSee('Visitantes recentes');
        $resposta->assertDontSee('Confirmações');
    }
}
