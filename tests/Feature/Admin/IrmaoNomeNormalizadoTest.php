<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\SituacaoCadastralIrmao;
use App\Models\Irmao;
use App\Models\User;
use Database\Seeders\PerfilPermissaoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * O nome completo é normalizado por mutator no model, de modo que cadastro e
 * edição compartilham exatamente a mesma regra de capitalização.
 */
class IrmaoNomeNormalizadoTest extends TestCase
{
    use RefreshDatabase;

    private function usuario(string ...$permissoes): User
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo(...$permissoes);

        return $usuario;
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private function payload(array $extra = []): array
    {
        return array_merge([
            'nome_completo' => 'Irmão de Teste',
            'situacao_cadastral' => SituacaoCadastralIrmao::ATIVO->value,
        ], $extra);
    }

    public function test_cadastro_normaliza_nome_enviado_em_maiusculas(): void
    {
        $usuario = $this->usuario('irmaos.criar');

        $this->actingAs($usuario)
            ->post(route('admin.irmaos.store'), $this->payload([
                'nome_completo' => 'JOSÉ MÁRCIO FERREIRA DA SILVA',
            ]))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('irmaos', ['nome_completo' => 'José Márcio Ferreira da Silva']);
    }

    public function test_cadastro_normaliza_espacos_excedentes(): void
    {
        $usuario = $this->usuario('irmaos.criar');

        $this->actingAs($usuario)
            ->post(route('admin.irmaos.store'), $this->payload([
                'nome_completo' => '  LUIS   CARLOS   ESTEVES  ',
            ]))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('irmaos', ['nome_completo' => 'Luis Carlos Esteves']);
    }

    public function test_edicao_normaliza_nome(): void
    {
        $usuario = $this->usuario('irmaos.editar');
        $irmao = Irmao::factory()->create(['cpf' => null]);

        $this->actingAs($usuario)
            ->put(route('admin.irmaos.update', $irmao), $this->payload([
                'nome_completo' => 'joão carlos dos santos',
            ]))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame('João Carlos dos Santos', $irmao->fresh()->nome_completo);
    }

    public function test_mutator_normaliza_na_atribuicao_direta(): void
    {
        $irmao = new Irmao;
        $irmao->nome_completo = 'LuIs CaRlOs EsTeVeS';

        $this->assertSame('Luis Carlos Esteves', $irmao->nome_completo);
    }

    public function test_mutator_preserva_hifen_e_apostrofo(): void
    {
        $irmao = Irmao::factory()->create([
            'cpf' => null,
            'nome_completo' => "ANA-MARIA D'ÁVILA DOS SANTOS",
        ]);

        $this->assertSame("Ana-Maria D'Ávila dos Santos", $irmao->fresh()->nome_completo);
    }

    public function test_normalizacao_do_nome_nao_afeta_o_cim(): void
    {
        $usuario = $this->usuario('irmaos.criar');

        $this->actingAs($usuario)
            ->post(route('admin.irmaos.store'), $this->payload([
                'nome_completo' => 'MARIA DE FÁTIMA',
                'cim' => '012345',
            ]))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $irmao = Irmao::query()->firstOrFail();

        $this->assertSame('Maria de Fátima', $irmao->nome_completo);
        $this->assertSame('012345', $irmao->cim);
        $this->assertDatabaseHas('irmaos', ['cim' => '012345']);
    }

    public function test_registro_existente_so_e_normalizado_quando_salvo(): void
    {
        // Simula o registro legado gravado antes do mutator existir.
        $irmao = Irmao::factory()->create(['cpf' => null]);
        Irmao::query()->whereKey($irmao->getKey())->update(['nome_completo' => 'LUIS CARLOS ESTEVES']);

        $this->assertSame('LUIS CARLOS ESTEVES', $irmao->fresh()->nome_completo);

        $recarregado = $irmao->fresh();
        $recarregado->nome_completo = $recarregado->nome_completo;
        $recarregado->save();

        $this->assertSame('Luis Carlos Esteves', $recarregado->fresh()->nome_completo);
    }
}
