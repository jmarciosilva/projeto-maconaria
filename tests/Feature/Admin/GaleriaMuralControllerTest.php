<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\StatusMuralGaleria;
use App\Enums\TipoReacaoMural;
use App\Enums\VisibilidadeMuralGaleria;
use App\Models\GaleriaAlbum;
use App\Models\MuralComentario;
use App\Models\MuralPublicacao;
use App\Models\MuralReacao;
use App\Models\User;
use Database\Seeders\PerfilPermissaoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class GaleriaMuralControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_permission_cannot_view_galeria_or_mural(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();

        $this->actingAs($usuario)->get(route('admin.galeria.albuns.index'))->assertForbidden();
        $this->actingAs($usuario)->get(route('admin.mural.publicacoes.index'))->assertForbidden();
    }

    public function test_user_with_permission_can_render_galeria_and_mural_pages(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo(
            'galeria.visualizar',
            'galeria.criar',
            'mural.visualizar',
            'mural.criar',
        );
        $album = GaleriaAlbum::factory()->publicado()->create(['titulo' => 'Álbum renderizado']);
        $publicacao = MuralPublicacao::factory()->publicado()->create(['titulo' => 'Publicação renderizada']);

        $this->actingAs($usuario)->get(route('admin.galeria.albuns.index'))->assertOk()->assertSee('Álbum renderizado');
        $this->actingAs($usuario)->get(route('admin.galeria.albuns.create'))->assertOk();
        $this->actingAs($usuario)->get(route('admin.galeria.albuns.show', $album))->assertOk()->assertSee('Álbum renderizado');
        $this->actingAs($usuario)->get(route('admin.mural.publicacoes.index'))->assertOk()->assertSee('Publicação renderizada');
        $this->actingAs($usuario)->get(route('admin.mural.publicacoes.create'))->assertOk();
        $this->actingAs($usuario)->get(route('admin.mural.publicacoes.show', $publicacao))->assertOk()->assertSee('Publicação renderizada');
    }

    public function test_user_with_permission_can_create_album_with_photo(): void
    {
        Storage::fake('public');
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('galeria.visualizar', 'galeria.criar');

        $this->actingAs($usuario)->post(route('admin.galeria.albuns.store'), [
            'titulo' => 'Sessão Magna',
            'status' => StatusMuralGaleria::PUBLICADO->value,
            'visibilidade' => VisibilidadeMuralGaleria::PUBLICA->value,
            'fotografias' => [UploadedFile::fake()->image('sessao.jpg')],
            'textos_alternativos' => ['Mesa diretora'],
        ])->assertRedirect();

        $album = GaleriaAlbum::where('titulo', 'Sessão Magna')->firstOrFail();
        $foto = $album->fotografias()->firstOrFail();

        $this->assertSame('sessao-magna', $album->slug);
        $this->assertSame(StatusMuralGaleria::PUBLICADO, $album->status);
        $this->assertSame('Mesa diretora', $foto->texto_alternativo);
        Storage::disk('public')->assertExists($foto->caminho);
        $this->assertDatabaseHas('auditorias', ['modulo' => 'galeria', 'acao' => 'criar']);
    }

    public function test_public_scope_excludes_restricted_gallery_albums(): void
    {
        GaleriaAlbum::factory()->publicado()->publico()->create();
        GaleriaAlbum::factory()->publicado()->create(['visibilidade' => VisibilidadeMuralGaleria::RESTRITA]);

        $this->assertCount(1, GaleriaAlbum::query()->publico()->get());
    }

    public function test_user_can_create_mural_publication_and_render_pages(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('mural.visualizar', 'mural.criar');

        $this->actingAs($usuario)->post(route('admin.mural.publicacoes.store'), [
            'titulo' => 'Aviso da semana',
            'conteudo' => 'Conteúdo do aviso.',
            'status' => StatusMuralGaleria::PUBLICADO->value,
            'visibilidade' => VisibilidadeMuralGaleria::RESTRITA->value,
        ])->assertRedirect();

        $publicacao = MuralPublicacao::where('titulo', 'Aviso da semana')->firstOrFail();

        $this->actingAs($usuario)->get(route('admin.mural.publicacoes.index'))->assertOk()->assertSee('Aviso da semana');
        $this->actingAs($usuario)->get(route('admin.mural.publicacoes.show', $publicacao))->assertOk()->assertSee('Conteúdo do aviso.');
        $this->assertDatabaseHas('auditorias', ['modulo' => 'mural', 'acao' => 'criar']);
    }

    public function test_comment_requires_moderation_and_can_be_approved(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $moderador = User::factory()->create();
        $usuario->givePermissionTo('mural.visualizar');
        $moderador->givePermissionTo('mural.visualizar', 'mural.moderar');
        $publicacao = MuralPublicacao::factory()->publicado()->create();

        $this->actingAs($usuario)->post(route('admin.mural.comentarios.store', $publicacao), [
            'comentario' => 'Comentário aguardando avaliação.',
        ])->assertRedirect();

        $comentario = MuralComentario::where('comentario', 'Comentário aguardando avaliação.')->firstOrFail();
        $this->assertFalse($comentario->aprovado);

        $this->actingAs($moderador)
            ->patch(route('admin.mural.comentarios.aprovar', $comentario))
            ->assertRedirect();

        $this->assertDatabaseHas('mural_comentarios', [
            'id' => $comentario->id,
            'aprovado' => true,
        ]);
    }

    public function test_reaction_is_unique_per_user_publication_and_type(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('mural.visualizar');
        $publicacao = MuralPublicacao::factory()->publicado()->create();

        $payload = ['tipo' => TipoReacaoMural::CURTIR->value];

        $this->actingAs($usuario)->post(route('admin.mural.reacoes.store', $publicacao), $payload)->assertRedirect();
        $this->actingAs($usuario)->post(route('admin.mural.reacoes.store', $publicacao), $payload)->assertRedirect();

        $this->assertSame(1, MuralReacao::query()
            ->where('publicacao_id', $publicacao->id)
            ->where('usuario_id', $usuario->id)
            ->where('tipo', TipoReacaoMural::CURTIR->value)
            ->count());
    }
}
