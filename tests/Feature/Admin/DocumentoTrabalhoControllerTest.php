<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\StatusDocumentoTrabalho;
use App\Enums\StatusEntregaDocumentoTrabalho;
use App\Models\DocumentoArquivo;
use App\Models\DocumentoAtividade;
use App\Models\DocumentoEntrega;
use App\Models\User;
use Database\Seeders\PerfilPermissaoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class DocumentoTrabalhoControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_permission_cannot_view_documents_module(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();

        $this->actingAs($usuario)
            ->get(route('admin.documentos.atividades.index'))
            ->assertForbidden();
    }

    public function test_user_with_permission_can_render_documents_pages(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('documentos.visualizar', 'documentos.avaliar', 'documentos.enviar');
        $atividade = DocumentoAtividade::factory()->publicada()->create(['titulo' => 'Atividade de leitura']);

        $this->actingAs($usuario)
            ->get(route('admin.documentos.atividades.index'))
            ->assertOk()
            ->assertSee('Atividade de leitura');

        $this->actingAs($usuario)
            ->get(route('admin.documentos.atividades.create'))
            ->assertOk();

        $this->actingAs($usuario)
            ->get(route('admin.documentos.atividades.show', $atividade))
            ->assertOk()
            ->assertSee('Atividade de leitura');
    }

    public function test_instructor_can_create_activity_with_private_attachment(): void
    {
        Storage::fake('local');
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('documentos.visualizar', 'documentos.avaliar');
        $arquivo = UploadedFile::fake()->create('orientacao.pdf', 128, 'application/pdf');

        $this->actingAs($usuario)->post(route('admin.documentos.atividades.store'), [
            'titulo' => 'Trabalho de instrução',
            'descricao' => 'Elaborar uma reflexão.',
            'status' => StatusDocumentoTrabalho::PUBLICADA->value,
            'prazo_entrega_em' => '2026-08-10 20:00',
            'arquivos' => [$arquivo],
        ])->assertRedirect();

        $atividade = DocumentoAtividade::where('titulo', 'Trabalho de instrução')->firstOrFail();
        $arquivoSalvo = $atividade->arquivos()->firstOrFail();

        $this->assertSame(StatusDocumentoTrabalho::PUBLICADA, $atividade->status);
        Storage::disk('local')->assertExists($arquivoSalvo->caminho);
        $this->assertDatabaseHas('auditorias', [
            'modulo' => 'documentos',
            'acao' => 'criar',
            'entidade' => 'DocumentoAtividade',
            'entidade_id' => $atividade->id,
        ]);
    }

    public function test_authorized_user_can_send_delivery_with_private_file(): void
    {
        Storage::fake('local');
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('documentos.enviar');
        $atividade = DocumentoAtividade::factory()->publicada()->create();
        $arquivo = UploadedFile::fake()->create('entrega.docx', 64, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $this->actingAs($usuario)->post(route('admin.documentos.entregas.store', $atividade), [
            'titulo' => 'Minha entrega',
            'descricao' => 'Documento enviado.',
            'arquivos' => [$arquivo],
        ])->assertRedirect();

        $entrega = DocumentoEntrega::where('titulo', 'Minha entrega')->firstOrFail();
        $arquivoSalvo = $entrega->arquivos()->firstOrFail();

        $this->assertSame($usuario->id, $entrega->usuario_id);
        $this->assertSame(StatusEntregaDocumentoTrabalho::ENVIADA, $entrega->status);
        Storage::disk('local')->assertExists($arquivoSalvo->caminho);
    }

    public function test_private_file_cannot_be_downloaded_by_unauthorized_user(): void
    {
        Storage::fake('local');
        $this->seed(PerfilPermissaoSeeder::class);

        $dono = User::factory()->create();
        $intruso = User::factory()->create();
        $entrega = DocumentoEntrega::factory()->create(['usuario_id' => $dono->id]);
        Storage::disk('local')->put('documentos/entregas/'.$entrega->id.'/privado.pdf', 'conteudo privado');
        $arquivo = DocumentoArquivo::create([
            'atividade_id' => $entrega->atividade_id,
            'entrega_id' => $entrega->id,
            'enviado_por_id' => $dono->id,
            'nome_original' => 'privado.pdf',
            'caminho' => 'documentos/entregas/'.$entrega->id.'/privado.pdf',
            'mime' => 'application/pdf',
            'tamanho' => 16,
        ]);

        $this->actingAs($intruso)
            ->get(route('admin.documentos.arquivos.baixar', $arquivo))
            ->assertForbidden();
    }

    public function test_owner_or_user_with_permission_can_download_private_file(): void
    {
        Storage::fake('local');
        $this->seed(PerfilPermissaoSeeder::class);

        $dono = User::factory()->create();
        $bibliotecario = User::factory()->create();
        $bibliotecario->givePermissionTo('documentos.visualizar');
        $entrega = DocumentoEntrega::factory()->create(['usuario_id' => $dono->id]);
        Storage::disk('local')->put('documentos/entregas/'.$entrega->id.'/privado.pdf', 'conteudo privado');
        $arquivo = DocumentoArquivo::create([
            'atividade_id' => $entrega->atividade_id,
            'entrega_id' => $entrega->id,
            'enviado_por_id' => $dono->id,
            'nome_original' => 'privado.pdf',
            'caminho' => 'documentos/entregas/'.$entrega->id.'/privado.pdf',
            'mime' => 'application/pdf',
            'tamanho' => 16,
        ]);

        $this->actingAs($dono)
            ->get(route('admin.documentos.arquivos.baixar', $arquivo))
            ->assertOk();

        $this->actingAs($bibliotecario)
            ->get(route('admin.documentos.arquivos.baixar', $arquivo))
            ->assertOk();
    }

    public function test_instructor_can_evaluate_delivery_and_register_comment(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('documentos.visualizar', 'documentos.avaliar');
        $entrega = DocumentoEntrega::factory()->create();

        $this->actingAs($usuario)->patch(route('admin.documentos.entregas.avaliar', $entrega), [
            'nota' => 90,
            'parecer' => 'Entrega adequada.',
        ])->assertRedirect();

        $this->assertDatabaseHas('documento_avaliacoes', [
            'entrega_id' => $entrega->id,
            'avaliador_id' => $usuario->id,
            'nota' => 90,
        ]);
        $this->assertDatabaseHas('documento_entregas', [
            'id' => $entrega->id,
            'status' => StatusEntregaDocumentoTrabalho::AVALIADA->value,
        ]);

        $this->actingAs($usuario)->post(route('admin.documentos.comentarios.store', $entrega->atividade), [
            'entrega_id' => $entrega->id,
            'comentario' => 'Comentário interno.',
        ])->assertRedirect();

        $this->assertDatabaseHas('documento_comentarios', [
            'atividade_id' => $entrega->atividade_id,
            'entrega_id' => $entrega->id,
            'comentario' => 'Comentário interno.',
        ]);
    }
}
