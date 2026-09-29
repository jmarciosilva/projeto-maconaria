<x-layouts.admin titulo="Editar sessão">
    <a href="{{ route('admin.chancelaria.sessoes.index') }}" class="text-sm font-medium text-blue-800 hover:underline">&larr; Voltar para sessões</a>

    @if ($totalFrequencias > 0)
        <div class="mt-4 max-w-3xl rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            Esta sessão possui <strong>{{ $totalFrequencias }}</strong> lançamento(s) de frequência. Alterar data, classe ou identificação
            não afeta os lançamentos — eles continuam vinculados a esta sessão. A exclusão, porém, fica bloqueada.
        </div>
    @endif

    <form method="POST" action="{{ route('admin.chancelaria.sessoes.update', $evento) }}" class="mt-4 max-w-3xl">
        @csrf
        @method('PUT')
        @include('admin.chancelaria.sessoes._form', ['evento' => $evento])

        <div class="mt-6 flex flex-wrap gap-3">
            <x-ui.button tipo="submit">Salvar alterações</x-ui.button>
            <a href="{{ route('admin.chancelaria.frequencias.edit', $evento) }}"><x-ui.button tipo="button" variante="secundario">Registrar frequência</x-ui.button></a>
        </div>
    </form>
</x-layouts.admin>
