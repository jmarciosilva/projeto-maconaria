<x-layouts.admin titulo="Novo item do carrossel">
    <form method="POST" action="{{ route('admin.carrossel.store') }}" enctype="multipart/form-data" class="max-w-2xl space-y-6 rounded-lg bg-white p-6 shadow-sm">
        @csrf

        @include('admin.carrossel._form')

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.carrossel.index') }}"><x-ui.button variante="secundario" tipo="button">Cancelar</x-ui.button></a>
            <x-ui.button tipo="submit">Salvar</x-ui.button>
        </div>
    </form>
</x-layouts.admin>
