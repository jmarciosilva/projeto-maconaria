<?php

namespace Tests\Feature\Admin;

use App\Enums\SituacaoCadastralIrmao;
use App\Models\Irmao;
use App\Models\User;
use Database\Seeders\PerfilPermissaoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * O CIM é opcional, mas quando informado deve ter exatamente 6 dígitos
 * numéricos e ser único. Continua armazenado como string, para preservar
 * eventuais zeros à esquerda.
 */
class IrmaoCimTest extends TestCase
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
     * @return array<string, mixed>
     */
    private function payload(array $extra = []): array
    {
        return array_merge([
            'nome_completo' => 'Irmão de Teste',
            'situacao_cadastral' => SituacaoCadastralIrmao::ATIVO->value,
        ], $extra);
    }

    public function test_cim_com_seis_digitos_e_aceito(): void
    {
        $usuario = $this->usuario('irmaos.criar');

        $this->actingAs($usuario)
            ->post(route('admin.irmaos.store'), $this->payload(['cim' => '123456']))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('irmaos', ['cim' => '123456']);
    }

    public function test_cim_com_menos_de_seis_digitos_e_rejeitado(): void
    {
        $usuario = $this->usuario('irmaos.criar');

        $this->actingAs($usuario)
            ->post(route('admin.irmaos.store'), $this->payload(['cim' => '12345']))
            ->assertSessionHasErrors('cim');

        $this->assertDatabaseCount('irmaos', 0);
    }

    public function test_cim_com_mais_de_seis_digitos_e_rejeitado(): void
    {
        $usuario = $this->usuario('irmaos.criar');

        $this->actingAs($usuario)
            ->post(route('admin.irmaos.store'), $this->payload(['cim' => '1234567']))
            ->assertSessionHasErrors('cim');

        $this->assertDatabaseCount('irmaos', 0);
    }

    public function test_cim_com_letras_e_rejeitado(): void
    {
        $usuario = $this->usuario('irmaos.criar');

        $this->actingAs($usuario)
            ->post(route('admin.irmaos.store'), $this->payload(['cim' => '12A456']))
            ->assertSessionHasErrors('cim');

        $this->assertDatabaseCount('irmaos', 0);
    }

    public function test_cim_duplicado_e_rejeitado(): void
    {
        $usuario = $this->usuario('irmaos.criar');
        Irmao::factory()->create(['cim' => '654321', 'cpf' => null]);

        $this->actingAs($usuario)
            ->post(route('admin.irmaos.store'), $this->payload(['cim' => '654321']))
            ->assertSessionHasErrors('cim');
    }

    public function test_cim_nulo_continua_permitido(): void
    {
        $usuario = $this->usuario('irmaos.criar');

        $this->actingAs($usuario)
            ->post(route('admin.irmaos.store'), $this->payload())
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('irmaos', ['nome_completo' => 'Irmão de Teste', 'cim' => null]);
    }

    public function test_cim_preserva_zeros_a_esquerda(): void
    {
        $usuario = $this->usuario('irmaos.criar');

        $this->actingAs($usuario)
            ->post(route('admin.irmaos.store'), $this->payload(['cim' => '012345']))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        // Continua string: não pode virar 12345.
        $this->assertDatabaseHas('irmaos', ['cim' => '012345']);
    }

    // ---------------- edição ----------------

    public function test_atualizacao_mantendo_o_proprio_cim_e_permitida(): void
    {
        $usuario = $this->usuario('irmaos.editar');
        $irmao = Irmao::factory()->create(['cim' => '777666', 'cpf' => null]);

        $this->actingAs($usuario)
            ->put(route('admin.irmaos.update', $irmao), $this->payload([
                'nome_completo' => 'Irmão Renomeado',
                'cim' => '777666',
            ]))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame('Irmão Renomeado', $irmao->fresh()->nome_completo);
    }

    public function test_atualizacao_com_cim_invalido_e_rejeitada(): void
    {
        $usuario = $this->usuario('irmaos.editar');
        $irmao = Irmao::factory()->create(['cim' => '777666', 'cpf' => null]);

        $this->actingAs($usuario)
            ->put(route('admin.irmaos.update', $irmao), $this->payload(['cim' => '77766']))
            ->assertSessionHasErrors('cim');

        $this->assertSame('777666', $irmao->fresh()->cim);
    }

    public function test_formulario_limita_o_campo_a_seis_digitos(): void
    {
        $usuario = $this->usuario('irmaos.criar');

        $resposta = $this->actingAs($usuario)->get(route('admin.irmaos.create'));

        $resposta->assertOk();
        $resposta->assertSee('maxlength="6"', false);
        $resposta->assertSee('inputmode="numeric"', false);
        $resposta->assertSee('Informe os 6 dígitos do CIM.');
    }
}
