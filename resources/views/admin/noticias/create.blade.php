<x-layouts.admin titulo="Nova Notícia">
    <form method="POST" action="{{ route('admin.noticias.store') }}" enctype="multipart/form-data" class="max-w-4xl">
        @csrf

        @include('admin.noticias._form')

        <div class="mt-6 flex gap-3">
            <x-ui.button tipo="submit">Salvar notícia</x-ui.button>
            <a href="{{ route('admin.noticias.index') }}">
                <x-ui.button variante="secundario">Cancelar</x-ui.button>
            </a>
        </div>
    </form>
</x-layouts.admin>
