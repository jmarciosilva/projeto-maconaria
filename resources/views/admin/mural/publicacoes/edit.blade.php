<x-layouts.admin titulo="Editar Publicação">
    <form method="POST" action="{{ route('admin.mural.publicacoes.update', $publicacao) }}" class="max-w-4xl space-y-6">
        @csrf
        @method('PUT')
        @include('admin.mural.publicacoes._form')
        <div class="flex gap-3"><x-ui.button tipo="submit">Salvar alterações</x-ui.button><a href="{{ route('admin.mural.publicacoes.show', $publicacao) }}"><x-ui.button variante="secundario">Voltar</x-ui.button></a></div>
    </form>
</x-layouts.admin>
