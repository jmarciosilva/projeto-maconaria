<?php

namespace Tests\Feature\Site;

use App\Enums\StatusMuralGaleria;
use App\Enums\TipoReacaoMural;
use App\Enums\VisibilidadeMuralGaleria;
use App\Models\CarrosselItem;
use App\Models\GaleriaAlbum;
use App\Models\GaleriaFotografia;
use App\Models\MuralComentario;
use App\Models\MuralPublicacao;
use App\Models\PaginaInstitucional;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaginaInicialTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_shows_fallback_hero_when_no_carrossel_items(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Augusta e Respeitável Loja Simbólica Ferraz de Vasconcelos');
    }

    public function test_home_page_shows_only_active_and_vigente_carrossel_items(): void
    {
        CarrosselItem::factory()->create([
            'titulo' => 'Slide Ativo',
            'ativo' => true,
        ]);

        CarrosselItem::factory()->create([
            'titulo' => 'Slide Inativo',
            'ativo' => false,
        ]);

        CarrosselItem::factory()->create([
            'titulo' => 'Slide Expirado',
            'ativo' => true,
            'data_fim' => now()->subDay(),
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Slide Ativo');
        $response->assertDontSee('Slide Inativo');
        $response->assertDontSee('Slide Expirado');
    }

    public function test_home_page_shows_published_institutional_pages(): void
    {
        PaginaInstitucional::factory()->create([
            'slug' => 'mudando-o-cidadao',
            'titulo' => 'Como a Maçonaria pode mudar um cidadão',
            'meta_descricao' => 'Uma chamada institucional para a home.',
            'publicado' => true,
        ]);

        PaginaInstitucional::factory()->create([
            'slug' => 'sobre-nos',
            'titulo' => 'Página em rascunho',
            'publicado' => false,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Como a Maçonaria pode mudar um cidadão')
            ->assertSee(route('paginas.mudar-cidadao'), false)
            ->assertDontSee('Página em rascunho');
    }

    public function test_home_page_shows_public_mural_feed_and_hides_restricted_posts(): void
    {
        $publica = MuralPublicacao::factory()->publicado()->publico()->create([
            'titulo' => 'Publicação pública no mural',
            'conteudo' => 'Conteúdo visível na home.',
        ]);
        MuralComentario::create([
            'publicacao_id' => $publica->id,
            'usuario_id' => User::factory()->create()->id,
            'comentario' => 'Comentário aprovado na home.',
            'aprovado' => true,
            'aprovado_em' => now(),
        ]);

        MuralPublicacao::factory()->publicado()->create([
            'titulo' => 'Publicação restrita no mural',
            'visibilidade' => VisibilidadeMuralGaleria::RESTRITA,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Mural da Loja')
            ->assertSee('Publicação pública no mural')
            ->assertSee('Conteúdo visível na home.')
            ->assertSee('Comentário aprovado na home.')
            ->assertDontSee('Publicação restrita no mural');
    }

    public function test_home_page_shows_public_gallery_and_hides_restricted_albums(): void
    {
        Storage::fake('public');

        $album = GaleriaAlbum::factory()->publicado()->publico()->create([
            'titulo' => 'Álbum público na home',
            'descricao' => 'Registro público da Loja.',
        ]);
        Storage::disk('public')->put('galeria/albuns/'.$album->id.'/foto.jpg', 'imagem');
        GaleriaFotografia::create([
            'album_id' => $album->id,
            'enviado_por_id' => User::factory()->create()->id,
            'texto_alternativo' => 'Foto pública',
            'caminho' => 'galeria/albuns/'.$album->id.'/foto.jpg',
            'mime' => 'image/jpeg',
            'tamanho' => 6,
        ]);

        GaleriaAlbum::factory()->publicado()->create([
            'titulo' => 'Álbum restrito',
            'visibilidade' => VisibilidadeMuralGaleria::RESTRITA,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Galeria da Loja')
            ->assertSee('Álbum público na home')
            ->assertSee('Registro público da Loja.')
            ->assertDontSee('Álbum restrito');
    }

    public function test_authenticated_user_can_like_and_comment_home_mural_post(): void
    {
        $usuario = User::factory()->create();
        $publicacao = MuralPublicacao::factory()->create([
            'status' => StatusMuralGaleria::PUBLICADO,
            'visibilidade' => VisibilidadeMuralGaleria::PUBLICA,
        ]);

        $this->actingAs($usuario)->post(route('mural.reacoes.store', $publicacao), [
            'tipo' => TipoReacaoMural::CURTIR->value,
        ])->assertRedirect();

        $this->actingAs($usuario)->post(route('mural.comentarios.store', $publicacao), [
            'comentario' => 'Comentário enviado pela home.',
        ])->assertRedirect();

        $this->assertDatabaseHas('mural_reacoes', [
            'publicacao_id' => $publicacao->id,
            'usuario_id' => $usuario->id,
            'tipo' => TipoReacaoMural::CURTIR->value,
        ]);
        $this->assertDatabaseHas('mural_comentarios', [
            'publicacao_id' => $publicacao->id,
            'usuario_id' => $usuario->id,
            'comentario' => 'Comentário enviado pela home.',
            'aprovado' => false,
        ]);
    }
}
