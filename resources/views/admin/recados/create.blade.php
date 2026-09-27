<x-layouts.admin titulo="Novo Recado">
    <form method="POST" action="{{ route('admin.recados.store') }}" class="max-w-4xl space-y-6">
        @csrf
        @include('admin.recados._form')
        <x-ui.button tipo="submit">Salvar recado</x-ui.button>
    </form>
</x-layouts.admin>
