<?php

namespace Tests\Feature\Admin;

use App\Enums\StatusNoticia;
use App\Enums\VisibilidadeNoticia;
use App\Models\Noticia;
use App\Models\NoticiaCategoria;
use App\Models\User;
use Database\Seeders\PerfilPermissaoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NoticiaControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_permission_cannot_view_news_admin(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();

        $this->actingAs($usuario)
            ->get(route('admin.noticias.index'))
            ->assertForbidden();
    }

    public function test_editor_can_create_draft_but_cannot_publish_without_permission(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $editor = User::factory()->create();
        $editor->givePermissionTo('noticias.visualizar', 'noticias.criar', 'noticias.editar');

        $this->actingAs($editor)->post(route('admin.noticias.store'), [
            'titulo' => 'Comunicado em rascunho',
            'slug' => 'comunicado-em-rascunho',
            'status' => StatusNoticia::RASCUNHO->value,
            'visibilidade' => VisibilidadeNoticia::PUBLICA->value,
            'conteudo' => '<p>Texto seguro.</p>',
        ])->assertRedirect();

        $this->assertDatabaseHas('noticias', [
            'slug' => 'comunicado-em-rascunho',
            'status' => StatusNoticia::RASCUNHO->value,
        ]);

        $this->actingAs($editor)->post(route('admin.noticias.store'), [
            'titulo' => 'Publicação não autorizada',
            'slug' => 'publicacao-nao-autorizada',
            'status' => StatusNoticia::PUBLICADA->value,
            'visibilidade' => VisibilidadeNoticia::PUBLICA->value,
        ])->assertSessionHasErrors('status');

        $this->assertDatabaseMissing('noticias', [
            'slug' => 'publicacao-nao-autorizada',
        ]);
    }

    public function test_authorized_user_can_publish_news_and_create_version_and_audit(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('noticias.criar', 'noticias.publicar');
        $categoria = NoticiaCategoria::factory()->create();

        $this->actingAs($usuario)->post(route('admin.noticias.store'), [
            'categoria_id' => $categoria->id,
            'titulo' => 'Notícia publicada',
            'slug' => 'noticia-publicada',
            'status' => StatusNoticia::PUBLICADA->value,
            'visibilidade' => VisibilidadeNoticia::PUBLICA->value,
            'destaque' => '1',
            'conteudo' => '<p>Conteúdo público.</p>',
        ])->assertRedirect();

        $noticia = Noticia::where('slug', 'noticia-publicada')->firstOrFail();

        $this->assertTrue($noticia->estaPublicaNoSite());
        $this->assertDatabaseHas('noticia_versoes', ['noticia_id' => $noticia->id, 'versao' => 1]);
        $this->assertDatabaseHas('auditorias', ['modulo' => 'noticias', 'entidade' => 'Noticia', 'entidade_id' => $noticia->id]);
    }

    public function test_news_slug_must_be_unique(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('noticias.criar');

        Noticia::factory()->create(['slug' => 'slug-repetido']);

        $this->actingAs($usuario)->post(route('admin.noticias.store'), [
            'titulo' => 'Outra notícia',
            'slug' => 'slug-repetido',
            'status' => StatusNoticia::RASCUNHO->value,
            'visibilidade' => VisibilidadeNoticia::PUBLICA->value,
        ])->assertSessionHasErrors('slug');
    }

    public function test_user_can_upload_a_cover_image_when_creating_a_news(): void
    {
        Storage::fake('public');
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('noticias.criar');

        $this->actingAs($usuario)->post(route('admin.noticias.store'), [
            'titulo' => 'Notícia com capa',
            'slug' => 'noticia-com-capa',
            'status' => StatusNoticia::RASCUNHO->value,
            'visibilidade' => VisibilidadeNoticia::PUBLICA->value,
            'imagem_capa' => UploadedFile::fake()->image('capa.jpg'),
        ])->assertRedirect();

        $noticia = Noticia::where('slug', 'noticia-com-capa')->firstOrFail();

        $this->assertNotNull($noticia->imagem_capa);
        Storage::disk('public')->assertExists($noticia->imagem_capa);
        $this->assertDatabaseHas('noticia_versoes', ['noticia_id' => $noticia->id, 'imagem_capa' => $noticia->imagem_capa]);
    }

    public function test_replacing_the_cover_image_deletes_the_previous_file(): void
    {
        Storage::fake('public');
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('noticias.criar', 'noticias.editar');

        $this->actingAs($usuario)->post(route('admin.noticias.store'), [
            'titulo' => 'Notícia para substituir capa',
            'slug' => 'noticia-substituir-capa',
            'status' => StatusNoticia::RASCUNHO->value,
            'visibilidade' => VisibilidadeNoticia::PUBLICA->value,
            'imagem_capa' => UploadedFile::fake()->image('original.jpg'),
        ]);

        $noticia = Noticia::where('slug', 'noticia-substituir-capa')->firstOrFail();
        $caminhoOriginal = $noticia->imagem_capa;

        $this->actingAs($usuario)->put(route('admin.noticias.update', $noticia), [
            'titulo' => $noticia->titulo,
            'slug' => $noticia->slug,
            'status' => StatusNoticia::RASCUNHO->value,
            'visibilidade' => VisibilidadeNoticia::PUBLICA->value,
            'imagem_capa' => UploadedFile::fake()->image('nova.jpg'),
        ])->assertRedirect();

        $noticia->refresh();

        $this->assertNotSame($caminhoOriginal, $noticia->imagem_capa);
        Storage::disk('public')->assertMissing($caminhoOriginal);
        Storage::disk('public')->assertExists($noticia->imagem_capa);
    }
}
