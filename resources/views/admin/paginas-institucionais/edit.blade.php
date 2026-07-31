<x-layouts.admin titulo="Editar página institucional">
    <form method="POST" action="{{ route('admin.paginas-institucionais.update', $pagina) }}" accept-charset="UTF-8" class="max-w-3xl">
        @csrf
        @method('PUT')

        @include('admin.paginas-institucionais._form')

        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('admin.paginas-institucionais.index') }}"><x-ui.button variante="secundario" tipo="button">Cancelar</x-ui.button></a>
            <x-ui.button tipo="submit">Salvar</x-ui.button>
        </div>
    </form>
</x-layouts.admin>
