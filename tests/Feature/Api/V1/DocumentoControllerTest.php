<?php

namespace Tests\Feature\Api\V1;

use App\Models\DocumentoAtividade;
use App\Models\DocumentoEntrega;
use App\Models\User;
use Database\Seeders\PerfilPermissaoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentoControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_requires_a_token(): void
    {
        $this->getJson(route('api.v1.documentos.index'))->assertUnauthorized();
    }

    /**
     * Exemplo obrigatório: usuário sem permissão não acessa (aqui,
     * Documentos e Trabalhos em vez de Tesouraria).
     */
    public function test_user_without_permission_cannot_list_atividades(): void
    {
        $usuario = User::factory()->create();

        $this->actingAs($usuario, 'sanctum')
            ->getJson(route('api.v1.documentos.index'))
            ->assertForbidden();
    }

    public function test_user_with_permission_can_list_and_view_atividade(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $atividade = DocumentoAtividade::factory()->publicada()->create();
        $usuario = User::factory()->create();
        $usuario->givePermissionTo('documentos.visualizar');

        $this->actingAs($usuario, 'sanctum')
            ->getJson(route('api.v1.documentos.index'))
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->actingAs($usuario, 'sanctum')
            ->getJson(route('api.v1.documentos.show', $atividade))
            ->assertOk()
            ->assertJsonPath('id', $atividade->id);
    }

    public function test_user_with_permission_can_submit_a_delivery(): void
    {
        Storage::fake('local');
        $this->seed(PerfilPermissaoSeeder::class);

        $atividade = DocumentoAtividade::factory()->publicada()->create();
        $usuario = User::factory()->create();
        $usuario->givePermissionTo('documentos.enviar');

        $this->actingAs($usuario, 'sanctum')
            ->postJson(route('api.v1.documentos.entregas.store', $atividade), [
                'titulo' => 'Minha entrega',
                'descricao' => 'Descrição da entrega.',
                'arquivos' => [UploadedFile::fake()->create('trabalho.pdf', 100, 'application/pdf')],
            ])
            ->assertCreated();

        $this->assertDatabaseHas('documento_entregas', [
            'atividade_id' => $atividade->id,
            'usuario_id' => $usuario->id,
            'titulo' => 'Minha entrega',
        ]);
    }

    public function test_user_without_enviar_permission_cannot_submit_a_delivery(): void
    {
        Storage::fake('local');
        $this->seed(PerfilPermissaoSeeder::class);

        $atividade = DocumentoAtividade::factory()->publicada()->create();
        $usuario = User::factory()->create();
        $usuario->givePermissionTo('documentos.visualizar');

        $this->actingAs($usuario, 'sanctum')
            ->postJson(route('api.v1.documentos.entregas.store', $atividade), [
                'titulo' => 'Minha entrega',
                'arquivos' => [UploadedFile::fake()->create('trabalho.pdf', 100, 'application/pdf')],
            ])
            ->assertForbidden();
    }

    /**
     * Exemplo obrigatório: documento privado não pode ser baixado por
     * usuário não autorizado (versão API).
     */
    public function test_private_file_cannot_be_downloaded_by_unauthorized_user(): void
    {
        Storage::fake('local');
        $this->seed(PerfilPermissaoSeeder::class);

        $entrega = DocumentoEntrega::factory()->create();
        Storage::disk('local')->put('documentos/entregas/'.$entrega->id.'/privado.pdf', 'conteudo privado');
        $arquivo = $entrega->arquivos()->create([
            'atividade_id' => $entrega->atividade_id,
            'enviado_por_id' => $entrega->usuario_id,
            'nome_original' => 'privado.pdf',
            'caminho' => 'documentos/entregas/'.$entrega->id.'/privado.pdf',
            'mime' => 'application/pdf',
            'tamanho' => 17,
        ]);

        $outroUsuario = User::factory()->create();

        $this->actingAs($outroUsuario, 'sanctum')
            ->getJson(route('api.v1.documentos.arquivos.baixar', $arquivo))
            ->assertForbidden();
    }

    public function test_owner_of_the_delivery_can_download_own_file(): void
    {
        Storage::fake('local');

        $entrega = DocumentoEntrega::factory()->create();
        Storage::disk('local')->put('documentos/entregas/'.$entrega->id.'/meu-arquivo.pdf', 'conteudo');
        $arquivo = $entrega->arquivos()->create([
            'atividade_id' => $entrega->atividade_id,
            'enviado_por_id' => $entrega->usuario_id,
            'nome_original' => 'meu-arquivo.pdf',
            'caminho' => 'documentos/entregas/'.$entrega->id.'/meu-arquivo.pdf',
            'mime' => 'application/pdf',
            'tamanho' => 8,
        ]);

        $this->actingAs($entrega->usuario, 'sanctum')
            ->getJson(route('api.v1.documentos.arquivos.baixar', $arquivo))
            ->assertOk();
    }
}
