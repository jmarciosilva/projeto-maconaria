<x-layouts.admin titulo="Novo Documento da Secretaria">
    <form method="POST" action="{{ route('admin.secretaria.documentos.store') }}" enctype="multipart/form-data" class="max-w-4xl">
        @csrf

        @include('admin.secretaria.documentos._form')

        <div class="mt-6 flex gap-3">
            <x-ui.button tipo="submit">Salvar documento</x-ui.button>
            <a href="{{ route('admin.secretaria.documentos.index') }}">
                <x-ui.button variante="secundario">Cancelar</x-ui.button>
            </a>
        </div>
    </form>
</x-layouts.admin>
