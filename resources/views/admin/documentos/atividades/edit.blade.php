<x-layouts.admin titulo="Editar Atividade">
    <form method="POST" action="{{ route('admin.documentos.atividades.update', $atividade) }}" enctype="multipart/form-data" class="max-w-4xl space-y-6">
        @csrf
        @method('PUT')

        @include('admin.documentos.atividades._form')

        <div class="flex flex-wrap gap-3">
            <x-ui.button tipo="submit">Salvar alterações</x-ui.button>
            <a href="{{ route('admin.documentos.atividades.show', $atividade) }}"><x-ui.button variante="secundario">Voltar</x-ui.button></a>
        </div>
    </form>
</x-layouts.admin>
