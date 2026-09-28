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

    public function test_photo_description_is_optional_when_creating_news(): void
    {
        Storage::fake('public');
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('noticias.criar');

        $this->actingAs($usuario)->post(route('admin.noticias.store'), [
            'titulo' => 'Notícia com fotos sem descrição',
            'slug' => 'noticia-fotos-sem-descricao',
            'status' => StatusNoticia::RASCUNHO->value,
            'visibilidade' => VisibilidadeNoticia::PUBLICA->value,
            'conteudo' => '<p>Conteúdo.</p>',
            'fotos' => [
                UploadedFile::fake()->image('foto1.jpg'),
                UploadedFile::fake()->image('foto2.jpg'),
            ],
            'fotos_descricao' => [],
        ])->assertRedirect();

        $noticia = Noticia::where('slug', 'noticia-fotos-sem-descricao')->firstOrFail();

        $this->assertCount(2, $noticia->fotos);
        $noticia->fotos->each(fn ($foto) => $this->assertNull($foto->descricao));
    }

    public function test_photo_description_is_optional_when_editing_news(): void
    {
        Storage::fake('public');
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('noticias.criar', 'noticias.editar');

        $noticia = Noticia::factory()->create();
        $noticia->fotos()->create(['caminho' => 'foto1.jpg', 'descricao' => 'descrição antiga', 'ordem' => 0]);

        $this->actingAs($usuario)->put(route('admin.noticias.update', $noticia), [
            'titulo' => $noticia->titulo,
            'slug' => $noticia->slug,
            'status' => StatusNoticia::RASCUNHO->value,
            'visibilidade' => VisibilidadeNoticia::PUBLICA->value,
            'conteudo' => '<p>Conteúdo.</p>',
            'fotos_descricao' => [
                'foto1.jpg' => '',
            ],
        ])->assertRedirect();

        $noticia->refresh();
        $this->assertEmpty($noticia->fotos->first()->descricao);
    }

    public function test_can_create_news_with_up_to_50_photos(): void
    {
        Storage::fake('public');
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('noticias.criar');

        $fotos = array_map(fn ($i) => UploadedFile::fake()->image("foto$i.jpg"), range(1, 50));

        $this->actingAs($usuario)->post(route('admin.noticias.store'), [
            'titulo' => 'Notícia com 50 fotos',
            'slug' => 'noticia-50-fotos',
            'status' => StatusNoticia::RASCUNHO->value,
            'visibilidade' => VisibilidadeNoticia::PUBLICA->value,
            'conteudo' => '<p>Conteúdo.</p>',
            'fotos' => $fotos,
        ])->assertRedirect();

        $noticia = Noticia::where('slug', 'noticia-50-fotos')->firstOrFail();

        $this->assertCount(50, $noticia->fotos);
    }

    public function test_cannot_create_news_with_more_than_50_photos(): void
    {
        Storage::fake('public');
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('noticias.criar');

        $fotos = array_map(fn ($i) => UploadedFile::fake()->image("foto$i.jpg"), range(1, 51));

        $this->actingAs($usuario)->post(route('admin.noticias.store'), [
            'titulo' => 'Notícia com 51 fotos',
            'slug' => 'noticia-51-fotos',
            'status' => StatusNoticia::RASCUNHO->value,
            'visibilidade' => VisibilidadeNoticia::PUBLICA->value,
            'conteudo' => '<p>Conteúdo.</p>',
            'fotos' => $fotos,
        ])->assertSessionHasErrors('fotos');

        $this->assertDatabaseMissing('noticias', ['slug' => 'noticia-51-fotos']);
    }

    public function test_cannot_exceed_50_photos_total_when_editing(): void
    {
        Storage::fake('public');
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('noticias.editar');

        $noticia = Noticia::factory()->create();

        for ($i = 0; $i < 35; $i++) {
            $noticia->fotos()->create(['caminho' => "foto$i.jpg", 'ordem' => $i]);
        }

        $novasFotos = array_map(fn ($i) => UploadedFile::fake()->image("nova$i.jpg"), range(1, 16));

        $this->actingAs($usuario)->put(route('admin.noticias.update', $noticia), [
            'titulo' => $noticia->titulo,
            'slug' => $noticia->slug,
            'status' => StatusNoticia::RASCUNHO->value,
            'visibilidade' => VisibilidadeNoticia::PUBLICA->value,
            'conteudo' => '<p>Conteúdo.</p>',
            'fotos' => $novasFotos,
        ])->assertSessionHasErrors('fotos');
    }

    public function test_can_add_photos_when_editing_keeping_total_under_50(): void
    {
        Storage::fake('public');
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('noticias.editar');

        $noticia = Noticia::factory()->create();

        for ($i = 0; $i < 35; $i++) {
            $noticia->fotos()->create(['caminho' => "foto$i.jpg", 'ordem' => $i]);
        }

        $novasFotos = array_map(fn ($i) => UploadedFile::fake()->image("nova$i.jpg"), range(1, 15));

        $this->actingAs($usuario)->put(route('admin.noticias.update', $noticia), [
            'titulo' => $noticia->titulo,
            'slug' => $noticia->slug,
            'status' => StatusNoticia::RASCUNHO->value,
            'visibilidade' => VisibilidadeNoticia::PUBLICA->value,
            'conteudo' => '<p>Conteúdo.</p>',
            'fotos' => $novasFotos,
        ])->assertRedirect();

        $noticia->refresh();

        $this->assertCount(50, $noticia->fotos);
    }

    public function test_can_remove_and_add_photos_maintaining_total_limit(): void
    {
        Storage::fake('public');
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('noticias.editar');

        $noticia = Noticia::factory()->create();

        for ($i = 0; $i < 35; $i++) {
            $noticia->fotos()->create(['caminho' => "foto$i.jpg", 'ordem' => $i]);
        }

        $fotosParaRemover = $noticia->fotos()->take(10)->pluck('id')->toArray();
        $novasFotos = array_map(fn ($i) => UploadedFile::fake()->image("nova$i.jpg"), range(1, 15));

        $this->actingAs($usuario)->put(route('admin.noticias.update', $noticia), [
            'titulo' => $noticia->titulo,
            'slug' => $noticia->slug,
            'status' => StatusNoticia::RASCUNHO->value,
            'visibilidade' => VisibilidadeNoticia::PUBLICA->value,
            'conteudo' => '<p>Conteúdo.</p>',
            'fotos' => $novasFotos,
            'fotos_para_remover' => $fotosParaRemover,
        ])->assertRedirect();

        $noticia->refresh();

        $this->assertCount(40, $noticia->fotos);
    }

    public function test_can_publish_news_with_retroactive_date(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('noticias.criar', 'noticias.publicar');

        $dataHistorica = now()->subMonths(6);

        $this->actingAs($usuario)->post(route('admin.noticias.store'), [
            'titulo' => 'Evento histórico da Loja',
            'slug' => 'evento-historico',
            'status' => StatusNoticia::PUBLICADA->value,
            'visibilidade' => VisibilidadeNoticia::PUBLICA->value,
            'conteudo' => '<p>Registramos um momento histórico.</p>',
            'publicado_em' => $dataHistorica->format('Y-m-d H:i'),
        ])->assertRedirect();

        $noticia = Noticia::where('slug', 'evento-historico')->firstOrFail();

        $this->assertTrue($dataHistorica->isSameAs($noticia->publicado_em, 'minute'));
    }

    public function test_can_edit_publication_date_of_already_published_news(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('noticias.editar');

        $noticia = Noticia::factory()->publicada()->create();
        $dataAnterior = $noticia->publicado_em->copy();
        $dataNovaHistorica = now()->subMonths(3);

        $this->actingAs($usuario)->put(route('admin.noticias.update', $noticia), [
            'titulo' => $noticia->titulo,
            'slug' => $noticia->slug,
            'status' => StatusNoticia::PUBLICADA->value,
            'visibilidade' => VisibilidadeNoticia::PUBLICA->value,
            'conteudo' => $noticia->conteudo,
            'publicado_em' => $dataNovaHistorica->format('Y-m-d H:i'),
        ])->assertRedirect();

        $noticia->refresh();

        $this->assertTrue($dataNovaHistorica->isSameAs($noticia->publicado_em, 'minute'));
        $this->assertNotEquals($dataAnterior, $noticia->publicado_em);
    }

    public function test_created_at_is_not_altered_when_setting_retroactive_publication_date(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('noticias.criar', 'noticias.publicar');

        $dataHistorica = now()->subYears(1);

        $this->actingAs($usuario)->post(route('admin.noticias.store'), [
            'titulo' => 'Notícia histórica',
            'slug' => 'noticia-historica',
            'status' => StatusNoticia::PUBLICADA->value,
            'visibilidade' => VisibilidadeNoticia::PUBLICA->value,
            'conteudo' => '<p>Histórico.</p>',
            'publicado_em' => $dataHistorica->format('Y-m-d H:i'),
        ])->assertRedirect();

        $noticia = Noticia::where('slug', 'noticia-historica')->firstOrFail();

        $this->assertTrue($dataHistorica->isSameAs($noticia->publicado_em, 'minute'));
        $this->assertTrue(now()->subMinutes(5)->isBefore($noticia->created_at));
    }

    public function test_editing_published_news_with_existing_photos_works_without_photo_input(): void
    {
        Storage::fake('public');
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('noticias.editar', 'noticias.publicar');

        $noticia = Noticia::factory()->publicada()->create();

        for ($i = 0; $i < 5; $i++) {
            $noticia->fotos()->create(['caminho' => "foto$i.jpg", 'descricao' => null, 'ordem' => $i]);
        }

        $this->actingAs($usuario)->put(route('admin.noticias.update', $noticia), [
            'titulo' => 'Título editado',
            'slug' => $noticia->slug,
            'status' => StatusNoticia::PUBLICADA->value,
            'visibilidade' => VisibilidadeNoticia::PUBLICA->value,
            'conteudo' => '<p>Conteúdo editado.</p>',
        ])->assertRedirect();

        $noticia->refresh();

        $this->assertCount(5, $noticia->fotos);
        $this->assertEquals('Título editado', $noticia->titulo);
    }

    public function test_public_news_ordering_respects_publication_date_not_creation_date(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        // Noticia criada hoje mas publicada em 2018 (histórica)
        $noticiaHistorica = Noticia::factory()->create([
            'status' => StatusNoticia::PUBLICADA,
            'visibilidade' => VisibilidadeNoticia::PUBLICA,
            'publicado_em' => now()->subYears(6)->setDate(2018, 3, 15)->setTime(20, 0),
        ]);

        // Noticia criada ontem mas publicada em 2025 (recente)
        $noticiaRecente = Noticia::factory()->create([
            'status' => StatusNoticia::PUBLICADA,
            'visibilidade' => VisibilidadeNoticia::PUBLICA,
            'publicado_em' => now()->subYears(1)->setDate(2025, 6, 10)->setTime(14, 30),
            'created_at' => now()->subDay(),
        ]);

        // Noticia criada hoje mas publicada há 3 anos
        $noticiaMeio = Noticia::factory()->create([
            'status' => StatusNoticia::PUBLICADA,
            'visibilidade' => VisibilidadeNoticia::PUBLICA,
            'publicado_em' => now()->subYears(3)->setDate(2020, 5, 10)->setTime(19, 30),
        ]);

        // Query pública deve ordenar por publicado_em DESC
        $noticias = Noticia::query()
            ->publicaNoSite()
            ->latest('publicado_em')
            ->get();

        // Esperado: Recente (2025) → Meio (2020) → Histórica (2018)
        $this->assertEquals($noticiaRecente->id, $noticias[0]->id);
        $this->assertEquals($noticiaMeio->id, $noticias[1]->id);
        $this->assertEquals($noticiaHistorica->id, $noticias[2]->id);
    }

    public function test_datetime_local_format_for_historical_news_edit(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('noticias.editar');

        $dataHistorica = now()->subYears(5)->setDate(2020, 5, 10)->setTime(19, 30);

        $noticia = Noticia::factory()->create([
            'status' => StatusNoticia::PUBLICADA,
            'publicado_em' => $dataHistorica,
        ]);

        $response = $this->actingAs($usuario)->get(route('admin.noticias.edit', $noticia));

        // Verificar que o formato está correto no HTML
        $response->assertSeeInOrder([
            'value="2020-05-10T19:30"',
        ]);
    }

    public function test_home_page_shows_photo_preview_with_up_to_4_photos(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $noticia = Noticia::factory()->create([
            'status' => StatusNoticia::PUBLICADA,
            'visibilidade' => VisibilidadeNoticia::PUBLICA,
            'destaque' => true,
            'publicado_em' => now(),
        ]);

        for ($i = 0; $i < 6; $i++) {
            $noticia->fotos()->create(['caminho' => "noticias/fotos/foto$i.jpg", 'ordem' => $i]);
        }

        $response = $this->get(route('site.index'));

        $response->assertOk();
        $response->assertSee('Fotografias');
        // Verify only 4 photos are shown in preview
        $response->assertSee('Ver todas as 6 fotos');
    }

    public function test_detail_page_shows_complete_photo_gallery(): void
    {
        $noticia = Noticia::factory()->create([
            'status' => StatusNoticia::PUBLICADA,
            'visibilidade' => VisibilidadeNoticia::PUBLICA,
            'slug' => 'noticia-com-galeria',
            'publicado_em' => now(),
        ]);

        for ($i = 0; $i < 7; $i++) {
            $noticia->fotos()->create([
                'caminho' => "noticias/fotos/foto$i.jpg",
                'descricao' => $i % 2 === 0 ? "Descrição da foto $i" : null,
                'ordem' => $i,
            ]);
        }

        $response = $this->get(route('noticias.mostrar', $noticia->slug));

        $response->assertOk();
        $response->assertSee('Fotografias');
        // Verify all photos are shown
        for ($i = 0; $i < 7; $i++) {
            $response->assertSee("foto$i.jpg");
        }
    }

    public function test_detail_page_without_photos_does_not_show_gallery(): void
    {
        $noticia = Noticia::factory()->create([
            'status' => StatusNoticia::PUBLICADA,
            'visibilidade' => VisibilidadeNoticia::PUBLICA,
            'slug' => 'noticia-sem-fotos',
            'publicado_em' => now(),
        ]);

        $response = $this->get(route('noticias.mostrar', $noticia->slug));

        $response->assertOk();
        $response->assertDontSee('Fotografias');
    }
}
