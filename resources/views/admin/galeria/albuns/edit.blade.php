<x-layouts.admin titulo="Editar Álbum">
    <form method="POST" action="{{ route('admin.galeria.albuns.update', $album) }}" enctype="multipart/form-data" class="max-w-4xl space-y-6">
        @csrf
        @method('PUT')
        @include('admin.galeria.albuns._form')
        <div class="flex gap-3"><x-ui.button tipo="submit">Salvar alterações</x-ui.button><a href="{{ route('admin.galeria.albuns.show', $album) }}"><x-ui.button variante="secundario">Voltar</x-ui.button></a></div>
    </form>
</x-layouts.admin>
