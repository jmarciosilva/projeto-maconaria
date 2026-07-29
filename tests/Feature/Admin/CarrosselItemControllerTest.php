<?php

namespace Tests\Feature\Admin;

use App\Models\CarrosselItem;
use App\Models\User;
use Database\Seeders\PerfilPermissaoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CarrosselItemControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_permission_cannot_view_carrossel(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();

        $this->actingAs($usuario)
            ->get(route('admin.carrossel.index'))
            ->assertForbidden();
    }

    public function test_user_with_permission_can_create_carrossel_item(): void
    {
        Storage::fake('public');

        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('cms.editar', 'cms.visualizar');

        $this->actingAs($usuario)->post(route('admin.carrossel.store'), [
            'titulo' => 'Bem-vindos',
            'texto_alternativo' => 'Fachada da Loja',
            'ativo' => '1',
            'imagem_desktop' => UploadedFile::fake()->image('slide.jpg'),
        ])->assertRedirect(route('admin.carrossel.index'));

        $item = CarrosselItem::firstOrFail();

        $this->assertSame('Bem-vindos', $item->titulo);
        $this->assertTrue($item->ativo);
        Storage::disk('public')->assertExists($item->imagem_desktop);
    }

    public function test_imagem_desktop_is_required_on_create(): void
    {
        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('cms.editar');

        $this->actingAs($usuario)
            ->post(route('admin.carrossel.store'), [
                'texto_alternativo' => 'Sem imagem',
            ])
            ->assertSessionHasErrors('imagem_desktop');

        $this->assertDatabaseCount('carrossel_itens', 0);
    }

    public function test_user_with_permission_can_delete_carrossel_item(): void
    {
        Storage::fake('public');

        $this->seed(PerfilPermissaoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->givePermissionTo('cms.editar', 'cms.visualizar');

        $item = CarrosselItem::factory()->create();

        $this->actingAs($usuario)
            ->delete(route('admin.carrossel.destroy', $item))
            ->assertRedirect(route('admin.carrossel.index'));

        $this->assertDatabaseMissing('carrossel_itens', ['id' => $item->id]);
    }

    public function test_scope_ativo_excludes_inactive_items(): void
    {
        CarrosselItem::factory()->create(['ativo' => true]);
        CarrosselItem::factory()->create(['ativo' => false]);

        $this->assertCount(1, CarrosselItem::query()->ativo()->get());
    }

    public function test_scope_vigente_excludes_items_outside_date_range(): void
    {
        CarrosselItem::factory()->create([
            'data_inicio' => now()->subDays(10),
            'data_fim' => now()->subDay(),
        ]);

        CarrosselItem::factory()->create([
            'data_inicio' => now()->addDay(),
            'data_fim' => now()->addDays(10),
        ]);

        CarrosselItem::factory()->create([
            'data_inicio' => null,
            'data_fim' => null,
        ]);

        $this->assertCount(1, CarrosselItem::query()->vigente()->get());
    }
}
