<x-layouts.admin titulo="Nova Atividade">
    <form method="POST" action="{{ route('admin.documentos.atividades.store') }}" enctype="multipart/form-data" class="max-w-4xl space-y-6">
        @csrf

        @include('admin.documentos.atividades._form')

        <div class="flex flex-wrap gap-3">
            <x-ui.button tipo="submit">Salvar atividade</x-ui.button>
            <a href="{{ route('admin.documentos.atividades.index') }}"><x-ui.button variante="secundario">Voltar</x-ui.button></a>
        </div>
    </form>
</x-layouts.admin>
