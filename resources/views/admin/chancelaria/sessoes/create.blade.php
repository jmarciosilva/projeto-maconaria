<x-layouts.admin titulo="Nova sessão">
    <a href="{{ route('admin.chancelaria.sessoes.index') }}" class="text-sm font-medium text-blue-800 hover:underline">&larr; Voltar para sessões</a>

    <form method="POST" action="{{ route('admin.chancelaria.sessoes.store') }}" class="mt-4 max-w-3xl">
        @csrf
        @include('admin.chancelaria.sessoes._form', ['evento' => null])

        <div class="mt-6 flex flex-wrap gap-3">
            <x-ui.button tipo="submit" name="acao" value="salvar">Salvar</x-ui.button>
            <x-ui.button tipo="submit" name="acao" value="frequencia" variante="secundario">Salvar e registrar frequência</x-ui.button>
        </div>
    </form>
</x-layouts.admin>
