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
 * Área da Chancelaria para administrar SOMENTE sessões (Evento tipo=SESSAO),
 * sem dar ao Chanceler acesso ao CRUD geral de Eventos.
 */
class ChancelariaSessaoTest extends TestCase
{
    use RefreshDatabase;

    private function chanceler(): User
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->assignRole('Chanceler');

        return $usuario;
    }

    private function administrador(): User
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->assignRole('Administrador');

        return $usuario;
    }

    private function semPermissao(): User
    {
        $this->seed(PerfilPermissaoSeeder::class);

        return User::factory()->create();
    }

    private function sessao(string $data = '2025-01-15 20:00'): Evento
    {
        return Evento::factory()->create([
            'tipo' => TipoEvento::SESSAO,
            'status' => StatusEvento::PUBLICADO,
            'visibilidade' => VisibilidadeEvento::RESTRITA,
            'inicio_em' => Carbon::parse($data),
        ]);
    }

    private function eventoComum(): Evento
    {
        return Evento::factory()->create([
            'titulo' => 'Jantar Social da Loja',
            'tipo' => TipoEvento::EVENTO,
            'status' => StatusEvento::PUBLICADO,
            'visibilidade' => VisibilidadeEvento::PUBLICA,
            'inicio_em' => Carbon::parse('2025-06-10 20:00'),
        ]);
    }

    // ---------------- acesso ----------------

    public function test_administrador_acessa_sessoes_da_chancelaria(): void
    {
        $this->actingAs($this->administrador())
            ->get(route('admin.chancelaria.sessoes.index'))
            ->assertOk();
    }

    public function test_chanceler_acessa_sessoes_da_chancelaria(): void
    {
        $this->actingAs($this->chanceler())
            ->get(route('admin.chancelaria.sessoes.index'))
            ->assertOk();
    }

    public function test_usuario_sem_permissao_nao_acessa_sessoes(): void
    {
        $this->actingAs($this->semPermissao())
            ->get(route('admin.chancelaria.sessoes.index'))
            ->assertForbidden();
    }

    public function test_chanceler_nao_ganha_acesso_ao_crud_geral_de_eventos(): void
    {
        $this->actingAs($this->chanceler())
            ->get(route('admin.eventos.index'))
            ->assertForbidden();
    }

    // ---------------- criação ----------------

    public function test_chanceler_cadastra_sessao_passada(): void
    {
        $this->actingAs($this->chanceler())
            ->post(route('admin.chancelaria.sessoes.store'), [
                'inicio_em' => '2025-01-15 20:00',
                'sessao_classe' => ClasseSessao::ORDINARIA->value,
            ])->assertRedirect(route('admin.chancelaria.sessoes.index'));

        $evento = Evento::query()->firstOrFail();

        $this->assertSame('2025-01-15', $evento->inicio_em->toDateString());
        $this->assertSame(ClasseSessao::ORDINARIA, $evento->sessao_classe);
    }

    public function test_chanceler_cadastra_sessao_futura(): void
    {
        $futuro = now()->addMonths(2)->format('Y-m-d H:i');

        $this->actingAs($this->chanceler())
            ->post(route('admin.chancelaria.sessoes.store'), ['inicio_em' => $futuro])
            ->assertRedirect();

        $this->assertTrue(Evento::query()->firstOrFail()->inicio_em->isFuture());
    }

    public function test_sessao_criada_tem_sempre_tipo_sessao_e_e_restrita(): void
    {
        $this->actingAs($this->chanceler())
            ->post(route('admin.chancelaria.sessoes.store'), ['inicio_em' => '2025-02-12 20:00'])
            ->assertRedirect();

        $evento = Evento::query()->firstOrFail();

        $this->assertSame(TipoEvento::SESSAO, $evento->tipo);
        $this->assertSame(VisibilidadeEvento::RESTRITA, $evento->visibilidade);
    }

    public function test_tentativa_de_enviar_tipo_evento_nao_altera_o_tipo(): void
    {
        $this->actingAs($this->chanceler())
            ->post(route('admin.chancelaria.sessoes.store'), [
                'inicio_em' => '2025-03-12 20:00',
                'tipo' => TipoEvento::EVENTO->value,
            ])->assertRedirect();

        $this->assertSame(TipoEvento::SESSAO, Evento::query()->firstOrFail()->tipo);
    }

    public function test_tentativa_de_tornar_sessao_publica_e_ignorada(): void
    {
        $this->actingAs($this->chanceler())
            ->post(route('admin.chancelaria.sessoes.store'), [
                'inicio_em' => '2025-04-12 20:00',
                'visibilidade' => VisibilidadeEvento::PUBLICA->value,
            ])->assertRedirect();

        $evento = Evento::query()->firstOrFail();

        $this->assertSame(VisibilidadeEvento::RESTRITA, $evento->visibilidade);
        $this->assertNotContains($evento->id, Evento::query()->publicoNoSite()->pluck('id')->all());
    }

    public function test_salvar_e_registrar_frequencia_redireciona_para_o_lancamento(): void
    {
        $this->actingAs($this->chanceler())
            ->post(route('admin.chancelaria.sessoes.store'), [
                'inicio_em' => '2025-05-12 20:00',
                'acao' => 'frequencia',
            ])->assertRedirect(route('admin.chancelaria.frequencias.edit', Evento::query()->firstOrFail()));
    }

    public function test_sessao_criada_aparece_no_fluxo_existente_de_frequencia(): void
    {
        $usuario = $this->chanceler();

        $this->actingAs($usuario)->post(route('admin.chancelaria.sessoes.store'), [
            'inicio_em' => '2025-07-12 20:00',
            'titulo' => 'Sessao Criada Pela Nova Area',
        ])->assertRedirect();

        $this->actingAs($usuario)
            ->get(route('admin.chancelaria.frequencias.selecionar-evento'))
            ->assertOk()
            ->assertSee('Sessao Criada Pela Nova Area');
    }

    // ---------------- listagem ----------------

    public function test_eventos_comuns_nao_aparecem_na_listagem_de_sessoes(): void
    {
        $sessao = $this->sessao();
        $this->eventoComum();

        $this->actingAs($this->chanceler())
            ->get(route('admin.chancelaria.sessoes.index'))
            ->assertOk()
            ->assertSee($sessao->titulo)
            ->assertDontSee('Jantar Social da Loja');
    }

    public function test_filtro_por_ano_funciona(): void
    {
        $de2025 = $this->sessao('2025-08-10 20:00');
        $de2026 = $this->sessao('2026-08-10 20:00');

        $this->actingAs($this->chanceler())
            ->get(route('admin.chancelaria.sessoes.index', ['ano' => 2025]))
            ->assertOk()
            ->assertSee($de2025->titulo)
            ->assertDontSee($de2026->titulo);
    }

    // ---------------- edição ----------------

    public function test_chanceler_edita_uma_sessao(): void
    {
        $sessao = $this->sessao('2025-09-10 20:00');

        $this->actingAs($this->chanceler())
            ->put(route('admin.chancelaria.sessoes.update', $sessao), [
                'inicio_em' => '2025-09-17 20:30',
                'sessao_classe' => ClasseSessao::MAGNA->value,
                'titulo' => 'Sessão corrigida',
            ])->assertRedirect(route('admin.chancelaria.sessoes.index'));

        $sessao->refresh();

        $this->assertSame('2025-09-17 20:30', $sessao->inicio_em->format('Y-m-d H:i'));
        $this->assertSame(ClasseSessao::MAGNA, $sessao->sessao_classe);
        $this->assertSame(TipoEvento::SESSAO, $sessao->tipo);
    }

    public function test_edicao_nao_transforma_sessao_em_evento_comum(): void
    {
        $sessao = $this->sessao();

        $this->actingAs($this->chanceler())
            ->put(route('admin.chancelaria.sessoes.update', $sessao), [
                'inicio_em' => '2025-10-10 20:00',
                'tipo' => TipoEvento::EVENTO->value,
                'visibilidade' => VisibilidadeEvento::PUBLICA->value,
            ])->assertRedirect();

        $sessao->refresh();

        $this->assertSame(TipoEvento::SESSAO, $sessao->tipo);
        $this->assertSame(VisibilidadeEvento::RESTRITA, $sessao->visibilidade);
    }

    public function test_chanceler_nao_edita_evento_comum_por_esta_area(): void
    {
        $evento = $this->eventoComum();

        $this->actingAs($this->chanceler())
            ->get(route('admin.chancelaria.sessoes.edit', $evento))
            ->assertNotFound();

        $this->actingAs($this->chanceler())
            ->put(route('admin.chancelaria.sessoes.update', $evento), ['inicio_em' => '2025-11-10 20:00'])
            ->assertNotFound();

        $this->assertSame('Jantar Social da Loja', $evento->fresh()->titulo);
    }

    // ---------------- exclusão ----------------

    public function test_chanceler_nao_exclui_evento_comum_por_esta_area(): void
    {
        $evento = $this->eventoComum();

        $this->actingAs($this->chanceler())
            ->delete(route('admin.chancelaria.sessoes.destroy', $evento))
            ->assertNotFound();

        $this->assertNotNull($evento->fresh());
    }

    public function test_sessao_com_frequencia_nao_pode_ser_excluida(): void
    {
        $sessao = $this->sessao();
        $irmao = Irmao::factory()->create(['cpf' => null, 'cim' => '123456']);

        ChancelariaFrequencia::create([
            'evento_id' => $sessao->id,
            'irmao_id' => $irmao->id,
            'status' => StatusFrequencia::PRESENTE,
        ]);

        $this->actingAs($this->chanceler())
            ->delete(route('admin.chancelaria.sessoes.destroy', $sessao))
            ->assertRedirect()
            ->assertSessionHas('erro');

        $this->assertNotNull($sessao->fresh(), 'A sessão não pode ser removida.');
        $this->assertDatabaseHas('chancelaria_frequencias', ['evento_id' => $sessao->id]);
    }

    public function test_sessao_sem_frequencia_pode_ser_excluida(): void
    {
        $sessao = $this->sessao();

        $this->actingAs($this->chanceler())
            ->delete(route('admin.chancelaria.sessoes.destroy', $sessao))
            ->assertRedirect()
            ->assertSessionHas('sucesso');

        // SoftDeletes: reversível, não destrutivo.
        $this->assertNull(Evento::query()->find($sessao->id));
        $this->assertNotNull(Evento::withTrashed()->find($sessao->id));
    }

    // ---------------- privacidade (MVP) ----------------

    public function test_protecao_publica_do_mvp_continua_funcionando(): void
    {
        $sessao = Evento::factory()->create([
            'tipo' => TipoEvento::SESSAO,
            'status' => StatusEvento::PUBLICADO,
            'visibilidade' => VisibilidadeEvento::PUBLICA, // forçada no banco
            'inicio_em' => Carbon::parse('2025-12-10 20:00'),
        ]);
        $publico = $this->eventoComum();

        $ids = Evento::query()->publicoNoSite()->pluck('id')->all();

        $this->assertNotContains($sessao->id, $ids);
        $this->assertContains($publico->id, $ids);
    }
}
