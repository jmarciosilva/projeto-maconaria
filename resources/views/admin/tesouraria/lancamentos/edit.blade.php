<x-layouts.admin titulo="Editar Lançamento">
    <form method="POST" action="{{ route('admin.tesouraria.lancamentos.update', $lancamento) }}" class="max-w-4xl space-y-6">@csrf @method('PUT') @include('admin.tesouraria.lancamentos._form') <x-ui.button tipo="submit">Salvar alterações</x-ui.button></form>
    <div class="mt-4 flex gap-3">
        @can('tesouraria.aprovar')<form method="POST" action="{{ route('admin.tesouraria.lancamentos.aprovar', $lancamento) }}">@csrf @method('PATCH')<x-ui.button variante="secundario" tipo="submit">Aprovar</x-ui.button></form>@endcan
        <form method="POST" action="{{ route('admin.tesouraria.lancamentos.baixar', $lancamento) }}">@csrf @method('PATCH')<x-ui.button tipo="submit">Baixar</x-ui.button></form>
    </div>
</x-layouts.admin>
