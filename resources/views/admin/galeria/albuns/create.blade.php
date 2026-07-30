<x-layouts.admin titulo="Novo Álbum">
    <form method="POST" action="{{ route('admin.galeria.albuns.store') }}" enctype="multipart/form-data" class="max-w-4xl space-y-6">
        @csrf
        @include('admin.galeria.albuns._form')
        <div class="flex gap-3"><x-ui.button tipo="submit">Salvar</x-ui.button><a href="{{ route('admin.galeria.albuns.index') }}"><x-ui.button variante="secundario">Voltar</x-ui.button></a></div>
    </form>
</x-layouts.admin>
