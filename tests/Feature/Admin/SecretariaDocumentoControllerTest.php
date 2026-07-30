<?php

namespace Tests\Feature\Admin;

use App\Enums\StatusDocumentoSecretaria;
use App\Enums\TipoDocumentoSecretaria;
use App\Models\SecretariaDocumento;
use App\Models\SecretariaDocumentoArquivo;
use App\Models\User;
use Database\Seeders\PerfilPermissaoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecretariaDocumentoControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_permission_cannot_view_secretaria(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();

        $this->actingAs($usuario)
            ->get(route('admin.secretaria.documentos.index'))
            ->assertForbidden();
    }

    public function test_secretario_can_create_document_with_number_and_version(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('secretaria.visualizar', 'secretaria.criar-ata', 'secretaria.editar-ata');

        $this->actingAs($usuario)->post(route('admin.secretaria.documentos.store'), [
            'tipo' => TipoDocumentoSecretaria::ATA->value,
            'titulo' => 'Ata da sessão ordinária',
            'conteudo' => 'Conteúdo da ata.',
            'status' => StatusDocumentoSecretaria::RASCUNHO->value,
            'data_documento' => '2026-07-30',
        ])->assertRedirect();

        $documento = SecretariaDocumento::where('codigo', 'ATA-2026-0001')->firstOrFail();

        $this->assertSame(1, $documento->numero);
        $this->assertDatabaseHas('secretaria_documento_versoes', [
            'documento_id' => $documento->id,
            'versao' => 1,
        ]);
        $this->assertDatabaseHas('auditorias', [
            'modulo' => 'secretaria',
            'entidade' => 'SecretariaDocumento',
            'entidade_id' => $documento->id,
        ]);
    }

    public function test_document_can_be_created_with_private_pdf_attachment(): void
    {
        Storage::fake('local');
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('secretaria.visualizar', 'secretaria.criar-ata', 'secretaria.editar-ata');

        $arquivo = UploadedFile::fake()->create('ata-assinada.pdf', 128, 'application/pdf');

        $this->actingAs($usuario)->post(route('admin.secretaria.documentos.store'), [
            'tipo' => TipoDocumentoSecretaria::ATA->value,
            'titulo' => 'Ata com arquivo',
            'conteudo' => '<p>Conteúdo pelo editor.</p>',
            'status' => StatusDocumentoSecretaria::RASCUNHO->value,
            'data_documento' => '2026-07-30',
            'arquivos' => [$arquivo],
        ])->assertRedirect();

        $documento = SecretariaDocumento::where('titulo', 'Ata com arquivo')->firstOrFail();
        $arquivoSalvo = $documento->arquivos()->firstOrFail();

        $this->assertSame('ata-assinada.pdf', $arquivoSalvo->nome_original);
        Storage::disk('local')->assertExists($arquivoSalvo->caminho);
    }

    public function test_document_content_created_in_editor_is_sanitized(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('secretaria.criar-ata');

        $this->actingAs($usuario)->post(route('admin.secretaria.documentos.store'), [
            'tipo' => TipoDocumentoSecretaria::ATA->value,
            'titulo' => 'Ata com HTML',
            'conteudo' => '<p>Texto seguro</p><script>alert(1)</script><a href="javascript:alert(1)">link</a>',
            'status' => StatusDocumentoSecretaria::RASCUNHO->value,
            'data_documento' => '2026-07-30',
        ])->assertRedirect();

        $documento = SecretariaDocumento::where('titulo', 'Ata com HTML')->firstOrFail();

        $this->assertStringContainsString('Texto seguro', $documento->conteudo);
        $this->assertStringNotContainsString('<script', $documento->conteudo);
        $this->assertStringNotContainsString('javascript:', $documento->conteudo);
    }

    public function test_attached_file_can_be_removed_from_private_storage(): void
    {
        Storage::fake('local');
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('secretaria.editar-ata');

        $documento = SecretariaDocumento::factory()->create();
        Storage::disk('local')->put('secretaria/documentos/'.$documento->id.'/ata.pdf', 'conteudo');
        $arquivo = SecretariaDocumentoArquivo::create([
            'documento_id' => $documento->id,
            'enviado_por_id' => $usuario->id,
            'nome_original' => 'ata.pdf',
            'caminho' => 'secretaria/documentos/'.$documento->id.'/ata.pdf',
            'mime' => 'application/pdf',
            'tamanho' => 8,
        ]);

        $this->actingAs($usuario)
            ->delete(route('admin.secretaria.documentos.arquivos.destroy', [$documento, $arquivo]))
            ->assertRedirect();

        Storage::disk('local')->assertMissing($arquivo->caminho);
        $this->assertDatabaseMissing('secretaria_documento_arquivos', ['id' => $arquivo->id]);
    }

    public function test_approval_and_publication_flow(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('secretaria.aprovar-ata', 'secretaria.publicar-ata');

        $documento = SecretariaDocumento::factory()->emAprovacao()->create();

        $this->actingAs($usuario)
            ->patch(route('admin.secretaria.documentos.aprovar', $documento))
            ->assertRedirect();

        $this->assertDatabaseHas('secretaria_documentos', [
            'id' => $documento->id,
            'status' => StatusDocumentoSecretaria::APROVADO->value,
            'aprovado_por_id' => $usuario->id,
        ]);

        $this->actingAs($usuario)
            ->patch(route('admin.secretaria.documentos.publicar', $documento))
            ->assertRedirect();

        $this->assertDatabaseHas('secretaria_documentos', [
            'id' => $documento->id,
            'status' => StatusDocumentoSecretaria::PUBLICADO->value,
            'publicado_por_id' => $usuario->id,
        ]);
    }

    public function test_approved_document_cannot_be_edited_directly(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('secretaria.editar-ata');

        $documento = SecretariaDocumento::factory()->aprovado()->create();

        $this->actingAs($usuario)->put(route('admin.secretaria.documentos.update', $documento), [
            'tipo' => TipoDocumentoSecretaria::ATA->value,
            'titulo' => 'Título alterado',
            'conteudo' => 'Conteúdo alterado.',
            'status' => StatusDocumentoSecretaria::RASCUNHO->value,
            'data_documento' => now()->toDateString(),
        ])->assertStatus(422);
    }
}
