<x-layouts.admin titulo="Nova Publicação">
    <form method="POST" action="{{ route('admin.mural.publicacoes.store') }}" enctype="multipart/form-data" class="max-w-4xl space-y-6">
        @csrf
        @include('admin.mural.publicacoes._form')
        <div class="flex gap-3"><x-ui.button tipo="submit">Salvar</x-ui.button><a href="{{ route('admin.mural.publicacoes.index') }}"><x-ui.button variante="secundario">Voltar</x-ui.button></a></div>
    </form>
</x-layouts.admin>
