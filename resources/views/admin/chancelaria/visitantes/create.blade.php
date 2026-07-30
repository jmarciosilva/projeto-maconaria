<x-layouts.admin titulo="Novo Visitante">
    <form method="POST" action="{{ route('admin.chancelaria.visitantes.store') }}" class="max-w-3xl space-y-6">
        @csrf
        @include('admin.chancelaria.visitantes._form')
        <x-ui.button tipo="submit">Salvar visitante</x-ui.button>
    </form>
</x-layouts.admin>
