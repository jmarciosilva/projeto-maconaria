<x-layouts.admin titulo="Novo Irmão">
    <form method="POST" action="{{ route('admin.irmaos.store') }}" enctype="multipart/form-data" class="max-w-3xl space-y-6 rounded-lg bg-white p-6 shadow-sm">
        @csrf

        @include('admin.irmaos._form', ['irmao' => null])

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.irmaos.index') }}"><x-ui.button variante="secundario" tipo="button">Cancelar</x-ui.button></a>
            <x-ui.button tipo="submit">Salvar</x-ui.button>
        </div>
    </form>
</x-layouts.admin>
