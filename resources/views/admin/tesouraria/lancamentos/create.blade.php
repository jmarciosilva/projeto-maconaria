<x-layouts.admin titulo="Novo Lançamento">
    <form method="POST" action="{{ route('admin.tesouraria.lancamentos.store') }}" class="max-w-4xl space-y-6">@csrf @include('admin.tesouraria.lancamentos._form') <x-ui.button tipo="submit">Salvar lançamento</x-ui.button></form>
</x-layouts.admin>
