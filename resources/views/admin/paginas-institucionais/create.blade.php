<x-layouts.admin titulo="Nova página institucional">
    <form method="POST" action="{{ route('admin.paginas-institucionais.store') }}" accept-charset="UTF-8" class="max-w-3xl space-y-6 rounded-lg bg-white p-6 shadow-sm">
        @csrf

        @include('admin.paginas-institucionais._form')

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.paginas-institucionais.index') }}"><x-ui.button variante="secundario" tipo="button">Cancelar</x-ui.button></a>
            <x-ui.button tipo="submit">Salvar</x-ui.button>
        </div>
    </form>
</x-layouts.admin>
