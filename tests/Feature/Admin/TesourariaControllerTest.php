<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\StatusLancamentoFinanceiro;
use App\Enums\TipoLancamentoFinanceiro;
use App\Models\Auditoria;
use App\Models\Irmao;
use App\Models\TesourariaCategoria;
use App\Models\TesourariaConta;
use App\Models\TesourariaFechamento;
use App\Models\TesourariaLancamento;
use App\Models\User;
use Database\Seeders\PerfilPermissaoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TesourariaControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_permission_cannot_view_tesouraria(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();

        $this->actingAs($usuario)
            ->get(route('admin.tesouraria.index'))
            ->assertForbidden();
    }

    public function test_user_can_create_financial_entry_with_audit(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('tesouraria.criar');
        $categoria = TesourariaCategoria::factory()->receita()->create();
        $conta = TesourariaConta::factory()->create();

        $this->actingAs($usuario)->post(route('admin.tesouraria.lancamentos.store'), [
            'categoria_id' => $categoria->id,
            'conta_id' => $conta->id,
            'tipo' => TipoLancamentoFinanceiro::RECEITA->value,
            'status' => StatusLancamentoFinanceiro::PENDENTE->value,
            'descricao' => 'Recebimento de mensalidade',
            'valor' => '150,75',
            'data_competencia' => '2026-07-01',
        ])->assertRedirect();

        $this->assertDatabaseHas('tesouraria_lancamentos', [
            'descricao' => 'Recebimento de mensalidade',
            'valor_centavos' => 15075,
            'criado_por_id' => $usuario->id,
        ]);
        $this->assertDatabaseHas('auditorias', [
            'modulo' => 'tesouraria',
            'acao' => 'criar',
        ]);
    }

    public function test_user_with_permission_can_render_tesouraria_pages(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('tesouraria.visualizar', 'tesouraria.criar', 'tesouraria.editar');
        TesourariaCategoria::factory()->receita()->create(['nome' => 'Mensalidades']);
        TesourariaConta::factory()->create(['nome' => 'Caixa']);

        $rotas = [
            route('admin.tesouraria.index'),
            route('admin.tesouraria.categorias.index'),
            route('admin.tesouraria.contas.index'),
            route('admin.tesouraria.lancamentos.index'),
            route('admin.tesouraria.lancamentos.create'),
            route('admin.tesouraria.mensalidades.create'),
            route('admin.tesouraria.fechamentos.index'),
        ];

        foreach ($rotas as $rota) {
            $this->actingAs($usuario)->get($rota)->assertOk();
        }
    }

    public function test_user_can_approve_and_settle_financial_entry(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('tesouraria.aprovar', 'tesouraria.editar');
        $lancamento = TesourariaLancamento::factory()->create([
            'data_competencia' => '2026-07-01',
        ]);

        $this->actingAs($usuario)
            ->patch(route('admin.tesouraria.lancamentos.aprovar', $lancamento))
            ->assertRedirect();

        $this->assertDatabaseHas('tesouraria_lancamentos', [
            'id' => $lancamento->id,
            'status' => StatusLancamentoFinanceiro::APROVADO->value,
            'aprovado_por_id' => $usuario->id,
        ]);

        $this->actingAs($usuario)
            ->patch(route('admin.tesouraria.lancamentos.baixar', $lancamento->refresh()))
            ->assertRedirect();

        $this->assertDatabaseHas('tesouraria_lancamentos', [
            'id' => $lancamento->id,
            'status' => StatusLancamentoFinanceiro::BAIXADO->value,
        ]);
        $this->assertDatabaseHas('auditorias', ['modulo' => 'tesouraria', 'acao' => 'aprovar']);
        $this->assertDatabaseHas('auditorias', ['modulo' => 'tesouraria', 'acao' => 'baixar']);
    }

    public function test_closed_period_blocks_financial_entry_and_rolls_back_audit(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('tesouraria.criar');
        $categoria = TesourariaCategoria::factory()->despesa()->create();
        $conta = TesourariaConta::factory()->create();
        TesourariaFechamento::create([
            'ano' => 2026,
            'mes' => 7,
            'fechado_por_id' => $usuario->id,
            'fechado_em' => now(),
        ]);

        $this->actingAs($usuario)->post(route('admin.tesouraria.lancamentos.store'), [
            'categoria_id' => $categoria->id,
            'conta_id' => $conta->id,
            'tipo' => TipoLancamentoFinanceiro::DESPESA->value,
            'status' => StatusLancamentoFinanceiro::PENDENTE->value,
            'descricao' => 'Despesa em período fechado',
            'valor' => '88,00',
            'data_competencia' => '2026-07-15',
        ])->assertSessionHas('erro');

        $this->assertDatabaseMissing('tesouraria_lancamentos', [
            'descricao' => 'Despesa em período fechado',
        ]);
        $this->assertSame(0, Auditoria::query()->where('modulo', 'tesouraria')->where('acao', 'criar')->count());
    }

    public function test_user_can_generate_monthly_dues_for_all_brothers(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('tesouraria.criar');
        $categoria = TesourariaCategoria::factory()->receita()->create(['nome' => 'Mensalidades']);
        $conta = TesourariaConta::factory()->create();
        Irmao::factory()->count(3)->create();

        $this->actingAs($usuario)->post(route('admin.tesouraria.mensalidades.store'), [
            'categoria_id' => $categoria->id,
            'conta_id' => $conta->id,
            'valor' => '120,00',
            'ano' => 2026,
            'mes' => 8,
        ])->assertRedirect(route('admin.tesouraria.lancamentos.index'));

        $this->assertSame(3, TesourariaLancamento::query()->where('categoria_id', $categoria->id)->count());
        $this->assertDatabaseHas('tesouraria_lancamentos', [
            'valor_centavos' => 12000,
            'status' => StatusLancamentoFinanceiro::PENDENTE->value,
            'data_competencia' => '2026-08-01 00:00:00',
        ]);
        $this->assertDatabaseHas('auditorias', ['modulo' => 'tesouraria', 'acao' => 'gerar-mensalidades']);
    }
}
