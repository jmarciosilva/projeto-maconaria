<x-layouts.admin titulo="Novo Evento">
    <form method="POST" action="{{ route('admin.eventos.store') }}" class="max-w-4xl space-y-6">
        @csrf

        @include('admin.eventos._form')

        <div class="flex gap-3">
            <x-ui.button tipo="submit">Salvar evento</x-ui.button>
            <a href="{{ route('admin.eventos.index') }}">
                <x-ui.button variante="secundario">Cancelar</x-ui.button>
            </a>
        </div>
    </form>
</x-layouts.admin>
