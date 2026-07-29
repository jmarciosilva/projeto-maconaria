<?php

namespace Tests\Feature\Admin;

use App\Enums\SituacaoCadastralIrmao;
use App\Models\Irmao;
use App\Models\User;
use Database\Seeders\PerfilPermissaoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class IrmaoControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Gera um CPF válido (dígitos verificadores corretos) a partir de uma
     * base de 9 dígitos, para não depender de CPFs de teste "mágicos".
     */
    private static function cpfValido(string $base9): string
    {
        $digitos = str_split($base9);

        for ($posicaoDigitoVerificador = 9; $posicaoDigitoVerificador < 11; $posicaoDigitoVerificador++) {
            $soma = 0;

            for ($posicao = 0; $posicao < $posicaoDigitoVerificador; $posicao++) {
                $soma += (int) $digitos[$posicao] * (($posicaoDigitoVerificador + 1) - $posicao);
            }

            $resto = ($soma * 10) % 11;
            $digitos[] = $resto === 10 ? 0 : $resto;
        }

        return implode('', $digitos);
    }

    public function test_user_without_permission_cannot_view_irmaos_list(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();

        $this->actingAs($usuario)
            ->get(route('admin.irmaos.index'))
            ->assertForbidden();
    }

    public function test_user_with_permission_can_view_irmaos_list(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('irmaos.visualizar');

        $this->actingAs($usuario)
            ->get(route('admin.irmaos.index'))
            ->assertOk();
    }

    public function test_can_create_irmao_with_valid_data(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('irmaos.criar');

        $cpf = self::cpfValido('123456789');

        $response = $this->actingAs($usuario)->post(route('admin.irmaos.store'), [
            'nome_completo' => 'João da Silva',
            'cpf' => $cpf,
            'situacao_cadastral' => SituacaoCadastralIrmao::ATIVO->value,
        ]);

        $irmao = Irmao::where('cpf', $cpf)->firstOrFail();

        $response->assertRedirect(route('admin.irmaos.show', $irmao));
        $this->assertSame('João da Silva', $irmao->nome_completo);
    }

    public function test_invalid_cpf_is_rejected(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('irmaos.criar');

        $this->actingAs($usuario)
            ->post(route('admin.irmaos.store'), [
                'nome_completo' => 'Maria Souza',
                'cpf' => '12345678900',
                'situacao_cadastral' => SituacaoCadastralIrmao::ATIVO->value,
            ])
            ->assertSessionHasErrors('cpf');

        $this->assertDatabaseCount('irmaos', 0);
    }

    public function test_duplicate_cpf_is_rejected(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('irmaos.criar');

        $cpf = self::cpfValido('987654321');

        Irmao::factory()->create(['cpf' => $cpf]);

        $this->actingAs($usuario)
            ->post(route('admin.irmaos.store'), [
                'nome_completo' => 'Outro Irmão',
                'cpf' => $cpf,
                'situacao_cadastral' => SituacaoCadastralIrmao::ATIVO->value,
            ])
            ->assertSessionHasErrors('cpf');

        $this->assertDatabaseCount('irmaos', 1);
    }

    public function test_irmao_without_linked_user_is_valid(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $irmao = Irmao::factory()->create();

        $this->assertNull($irmao->usuario);
        $this->assertTrue($irmao->exists);
    }

    public function test_updating_situacao_cadastral_creates_historico_record(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('irmaos.editar');

        $irmao = Irmao::factory()->create(['situacao_cadastral' => SituacaoCadastralIrmao::ATIVO]);

        $this->actingAs($usuario)->put(route('admin.irmaos.update', $irmao), [
            'nome_completo' => $irmao->nome_completo,
            'cpf' => $irmao->cpf,
            'situacao_cadastral' => SituacaoCadastralIrmao::LICENCIADO->value,
        ])->assertRedirect(route('admin.irmaos.show', $irmao));

        $this->assertDatabaseHas('irmao_historicos', [
            'irmao_id' => $irmao->id,
            'tipo' => 'situacao_cadastral',
            'valor_anterior' => 'ativo',
            'valor_novo' => 'licenciado',
        ]);
    }

    public function test_linking_user_to_irmao_sets_user_irmao_id(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('irmaos.criar');

        $usuarioParaVincular = User::factory()->create();

        $cpf = self::cpfValido('111222333');

        $this->actingAs($usuario)->post(route('admin.irmaos.store'), [
            'nome_completo' => 'Carlos Pereira',
            'cpf' => $cpf,
            'situacao_cadastral' => SituacaoCadastralIrmao::ATIVO->value,
            'usuario_id' => $usuarioParaVincular->id,
        ]);

        $this->assertSame(
            Irmao::where('cpf', $cpf)->firstOrFail()->id,
            $usuarioParaVincular->fresh()->irmao_id,
        );
    }

    public function test_cannot_link_user_already_linked_to_another_irmao(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('irmaos.criar');

        $irmaoExistente = Irmao::factory()->create();
        $usuarioJaVinculado = User::factory()->create(['irmao_id' => $irmaoExistente->id]);

        $this->actingAs($usuario)
            ->post(route('admin.irmaos.store'), [
                'nome_completo' => 'Novo Irmão',
                'cpf' => self::cpfValido('444555666'),
                'situacao_cadastral' => SituacaoCadastralIrmao::ATIVO->value,
                'usuario_id' => $usuarioJaVinculado->id,
            ])
            ->assertSessionHasErrors('usuario_id');
    }

    public function test_private_photo_cannot_be_downloaded_without_permission(): void
    {
        Storage::fake('local');

        $this->seed(PerfilPermissaoSeeder::class);

        $criador = User::factory()->create();
        $criador->givePermissionTo('irmaos.criar');

        $cpf = self::cpfValido('222333444');

        $this->actingAs($criador)->post(route('admin.irmaos.store'), [
            'nome_completo' => 'Irmão com Foto',
            'cpf' => $cpf,
            'situacao_cadastral' => SituacaoCadastralIrmao::ATIVO->value,
            'fotografia' => UploadedFile::fake()->image('foto.jpg'),
        ]);

        $irmao = Irmao::where('cpf', $cpf)->firstOrFail();

        $this->assertNotNull($irmao->fotografia);
        Storage::disk('local')->assertExists($irmao->fotografia);

        $semPermissao = User::factory()->create();

        $this->actingAs($semPermissao)
            ->get(route('admin.irmaos.foto', $irmao))
            ->assertForbidden();

        $comPermissao = User::factory()->create();
        $comPermissao->givePermissionTo('irmaos.visualizar');

        $this->actingAs($comPermissao)
            ->get(route('admin.irmaos.foto', $irmao))
            ->assertOk();
    }

    public function test_telefone_with_invalid_format_is_rejected(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('irmaos.criar');

        $this->actingAs($usuario)
            ->post(route('admin.irmaos.store'), [
                'nome_completo' => 'Irmão com Telefone Inválido',
                'cpf' => self::cpfValido('555666777'),
                'situacao_cadastral' => SituacaoCadastralIrmao::ATIVO->value,
                'telefone' => '11987654321',
            ])
            ->assertSessionHasErrors('telefone');

        $this->assertDatabaseCount('irmaos', 0);
    }

    public function test_telefone_with_valid_format_is_accepted(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('irmaos.criar');

        $cpf = self::cpfValido('777888999');

        $this->actingAs($usuario)->post(route('admin.irmaos.store'), [
            'nome_completo' => 'Irmão com Telefone Válido',
            'cpf' => $cpf,
            'situacao_cadastral' => SituacaoCadastralIrmao::ATIVO->value,
            'telefone' => '(11) 98765-4321',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('irmaos', [
            'cpf' => $cpf,
            'telefone' => '(11) 98765-4321',
        ]);
    }

    public function test_cep_with_invalid_format_is_rejected(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('irmaos.criar');

        $this->actingAs($usuario)
            ->post(route('admin.irmaos.store'), [
                'nome_completo' => 'Irmão com CEP Inválido',
                'cpf' => self::cpfValido('333222111'),
                'situacao_cadastral' => SituacaoCadastralIrmao::ATIVO->value,
                'cep' => '12345678',
            ])
            ->assertSessionHasErrors('cep');

        $this->assertDatabaseCount('irmaos', 0);
    }

    public function test_cep_with_valid_format_is_accepted(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('irmaos.criar');

        $cpf = self::cpfValido('999888777');

        $this->actingAs($usuario)->post(route('admin.irmaos.store'), [
            'nome_completo' => 'Irmão com CEP Válido',
            'cpf' => $cpf,
            'situacao_cadastral' => SituacaoCadastralIrmao::ATIVO->value,
            'cep' => '12345-678',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('irmaos', [
            'cpf' => $cpf,
            'cep' => '12345-678',
        ]);
    }
}
