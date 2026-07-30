<x-layouts.admin titulo="Novo Comunicado">
    <form method="POST" action="{{ route('admin.chancelaria.comunicados.store') }}" class="max-w-4xl space-y-6">
        @csrf
        @include('admin.chancelaria.comunicados._form')
        <x-ui.button tipo="submit">Salvar comunicado</x-ui.button>
    </form>
</x-layouts.admin>
