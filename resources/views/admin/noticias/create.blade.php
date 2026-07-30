<x-layouts.admin titulo="Nova Notícia">
    <form method="POST" action="{{ route('admin.noticias.store') }}" class="max-w-4xl space-y-6">
        @csrf

        @include('admin.noticias._form')

        <div class="flex gap-3">
            <x-ui.button tipo="submit">Salvar notícia</x-ui.button>
            <a href="{{ route('admin.noticias.index') }}">
                <x-ui.button variante="secundario">Cancelar</x-ui.button>
            </a>
        </div>
    </form>
</x-layouts.admin>
