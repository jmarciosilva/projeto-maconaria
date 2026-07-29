<?php

namespace Tests\Feature\Admin;

use App\Models\PaginaInstitucional;
use App\Models\User;
use Database\Seeders\PaginaInstitucionalSeeder;
use Database\Seeders\PerfilPermissaoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaginaInstitucionalControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_permission_cannot_view_paginas(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();

        $this->actingAs($usuario)
            ->get(route('admin.paginas-institucionais.index'))
            ->assertForbidden();
    }

    public function test_user_with_permission_can_create_pagina(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('cms.editar', 'cms.visualizar');

        $this->actingAs($usuario)->post(route('admin.paginas-institucionais.store'), [
            'titulo' => 'Pagina de Teste',
            'slug' => 'pagina-de-teste',
            'conteudo' => '<p>Ola mundo</p>',
            'publicado' => '1',
        ])->assertRedirect(route('admin.paginas-institucionais.index'));

        $this->assertDatabaseHas('paginas_institucionais', [
            'slug' => 'pagina-de-teste',
            'titulo' => 'Pagina de Teste',
        ]);
    }

    public function test_page_text_is_normalized_to_utf8_on_create(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('cms.editar', 'cms.visualizar');

        $this->actingAs($usuario)->post(route('admin.paginas-institucionais.store'), [
            'titulo' => "Sobre N\xF3s",
            'slug' => 'sobre-nos-teste',
            'conteudo' => "<p>Conte\xFAdo com acento.</p>",
            'publicado' => '1',
        ])->assertRedirect(route('admin.paginas-institucionais.index'));

        $this->assertDatabaseHas('paginas_institucionais', [
            'slug' => 'sobre-nos-teste',
            'titulo' => 'Sobre Nós',
            'conteudo' => '<p>Conteúdo com acento.</p>',
        ]);
    }

    public function test_mojibake_text_is_repaired_on_create(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('cms.editar', 'cms.visualizar');

        $this->actingAs($usuario)->post(route('admin.paginas-institucionais.store'), [
            'titulo' => 'ConclusÃ£o',
            'slug' => 'conclusao',
            'conteudo' => '<p>Nos dias de hoje, a MaÃ§onaria oferece uma base sÃ³lida de valores Ã©ticos.</p>',
            'publicado' => '1',
        ])->assertRedirect(route('admin.paginas-institucionais.index'));

        $this->assertDatabaseHas('paginas_institucionais', [
            'slug' => 'conclusao',
            'titulo' => 'Conclusão',
            'conteudo' => '<p>Nos dias de hoje, a Maçonaria oferece uma base sólida de valores éticos.</p>',
        ]);
    }

    public function test_slug_is_generated_from_title_when_empty_on_create(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('cms.editar', 'cms.visualizar');

        $this->actingAs($usuario)->post(route('admin.paginas-institucionais.store'), [
            'titulo' => 'Como a Maçonaria pode mudar um cidadão',
            'slug' => '',
            'conteudo' => '<p>Conteúdo institucional.</p>',
            'publicado' => '1',
        ])->assertRedirect(route('admin.paginas-institucionais.index'));

        $this->assertDatabaseHas('paginas_institucionais', [
            'slug' => 'como-a-maconaria-pode-mudar-um-cidadao',
            'titulo' => 'Como a Maçonaria pode mudar um cidadão',
        ]);
    }

    public function test_malicious_html_content_is_sanitized_on_store(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('cms.editar');

        $this->actingAs($usuario)->post(route('admin.paginas-institucionais.store'), [
            'titulo' => 'Pagina Maliciosa',
            'slug' => 'pagina-maliciosa',
            'conteudo' => '<p>Texto legitimo</p><script>alert(1)</script><img src=x onerror=alert(1)><a href="javascript:alert(1)">link</a>',
            'publicado' => '1',
        ]);

        $pagina = PaginaInstitucional::where('slug', 'pagina-maliciosa')->firstOrFail();

        $this->assertStringNotContainsString('<script', $pagina->conteudo);
        $this->assertStringNotContainsString('onerror', $pagina->conteudo);
        $this->assertStringNotContainsString('javascript:', $pagina->conteudo);
        $this->assertStringContainsString('Texto legitimo', $pagina->conteudo);
    }

    public function test_pasted_base64_image_is_stored_as_public_file_on_create(): void
    {
        Storage::fake('public');

        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('cms.editar');

        $imagemPngBase64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=';

        $this->actingAs($usuario)->post(route('admin.paginas-institucionais.store'), [
            'titulo' => 'Página com imagem',
            'slug' => 'pagina-com-imagem',
            'conteudo' => '<p>Antes da imagem</p><p><img src="data:image/png;base64,'.$imagemPngBase64.'"></p><p>Depois da imagem</p>',
            'publicado' => '1',
        ])->assertRedirect(route('admin.paginas-institucionais.index'));

        $pagina = PaginaInstitucional::where('slug', 'pagina-com-imagem')->firstOrFail();
        $arquivos = Storage::disk('public')->allFiles('paginas-institucionais/imagens');

        $this->assertCount(1, $arquivos);
        $this->assertStringNotContainsString('data:image', $pagina->conteudo);
        $this->assertStringContainsString('/storage/paginas-institucionais/imagens/', $pagina->conteudo);
        $this->assertStringContainsString('<img', $pagina->conteudo);
        $this->assertStringContainsString('Antes da imagem', $pagina->conteudo);
        $this->assertStringContainsString('Depois da imagem', $pagina->conteudo);
    }

    public function test_pasted_base64_image_keeps_utf8_accents_on_create(): void
    {
        Storage::fake('public');

        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('cms.editar');

        $imagemPngBase64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=';

        $this->actingAs($usuario)->post(route('admin.paginas-institucionais.store'), [
            'titulo' => 'Página com acentuação e imagem',
            'slug' => 'pagina-com-acentuacao-e-imagem',
            'conteudo' => '<p><strong>Conclusão!</strong></p><p>Nos dias de hoje, a Maçonaria oferece uma base sólida de valores éticos.</p><p><img src="data:image/png;base64,'.$imagemPngBase64.'"></p>',
            'publicado' => '1',
        ])->assertRedirect(route('admin.paginas-institucionais.index'));

        $pagina = PaginaInstitucional::where('slug', 'pagina-com-acentuacao-e-imagem')->firstOrFail();

        $this->assertStringContainsString('Conclusão!', $pagina->conteudo);
        $this->assertStringContainsString('Maçonaria', $pagina->conteudo);
        $this->assertStringContainsString('sólida', $pagina->conteudo);
        $this->assertStringContainsString('éticos', $pagina->conteudo);
        $this->assertStringNotContainsString('ConclusÃ£o', $pagina->conteudo);
        $this->assertStringNotContainsString('MaÃ§onaria', $pagina->conteudo);
        $this->assertStringContainsString('/storage/paginas-institucionais/imagens/', $pagina->conteudo);
    }

    public function test_user_with_permission_can_update_fixed_slug_page(): void
    {
        $this->seed([PerfilPermissaoSeeder::class, PaginaInstitucionalSeeder::class]);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('cms.editar');

        $pagina = PaginaInstitucional::where('slug', 'mudar-cidadao')->firstOrFail();

        $this->actingAs($usuario)->put(route('admin.paginas-institucionais.update', $pagina), [
            'titulo' => 'Como a Maçonaria pode mudar um cidadão',
            'slug' => 'mudar-cidadao',
            'conteudo' => '<p>Conteúdo atualizado pelo painel.</p>',
            'meta_descricao' => 'Texto de chamada atualizado.',
            'publicado' => '1',
        ])->assertRedirect(route('admin.paginas-institucionais.index'));

        $this->assertDatabaseHas('paginas_institucionais', [
            'id' => $pagina->id,
            'slug' => 'mudar-cidadao',
            'conteudo' => '<p>Conteúdo atualizado pelo painel.</p>',
            'meta_descricao' => 'Texto de chamada atualizado.',
            'publicado' => true,
        ]);
    }

    public function test_fixed_slug_pages_can_be_deleted(): void
    {
        $this->seed([PerfilPermissaoSeeder::class, PaginaInstitucionalSeeder::class]);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('cms.editar');

        $pagina = PaginaInstitucional::where('slug', 'sobre-nos')->firstOrFail();

        $this->actingAs($usuario)
            ->delete(route('admin.paginas-institucionais.destroy', $pagina))
            ->assertRedirect(route('admin.paginas-institucionais.index'));

        $this->assertDatabaseMissing('paginas_institucionais', ['slug' => 'sobre-nos']);
    }

    public function test_non_fixed_slug_page_can_be_deleted(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('cms.editar');

        $pagina = PaginaInstitucional::factory()->create(['slug' => 'pagina-livre']);

        $this->actingAs($usuario)
            ->delete(route('admin.paginas-institucionais.destroy', $pagina))
            ->assertRedirect(route('admin.paginas-institucionais.index'));

        $this->assertDatabaseMissing('paginas_institucionais', ['slug' => 'pagina-livre']);
    }

    public function test_slug_must_be_unique(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('cms.editar');

        PaginaInstitucional::factory()->create(['slug' => 'existente']);

        $this->actingAs($usuario)
            ->post(route('admin.paginas-institucionais.store'), [
                'titulo' => 'Outra',
                'slug' => 'existente',
                'publicado' => '1',
            ])
            ->assertSessionHasErrors('slug');
    }
}
