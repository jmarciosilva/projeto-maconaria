<x-layouts.admin titulo="Novo item do carrossel">
    <form method="POST" action="{{ route('admin.carrossel.store') }}" enctype="multipart/form-data" class="max-w-3xl">
        @csrf

        @include('admin.carrossel._form')

        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('admin.carrossel.index') }}"><x-ui.button variante="secundario" tipo="button">Cancelar</x-ui.button></a>
            <x-ui.button tipo="submit">Salvar</x-ui.button>
        </div>
    </form>
</x-layouts.admin>
