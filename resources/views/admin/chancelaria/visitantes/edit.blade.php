<x-layouts.admin titulo="Editar Visitante">
    <form method="POST" action="{{ route('admin.chancelaria.visitantes.update', $visitante) }}" class="max-w-3xl space-y-6">
        @csrf
        @method('PUT')
        @include('admin.chancelaria.visitantes._form')
        <div class="flex gap-3">
            <x-ui.button tipo="submit">Salvar alterações</x-ui.button>
            <x-ui.confirmation :acao="route('admin.chancelaria.visitantes.destroy', $visitante)" metodo="DELETE" titulo="Remover visitante" mensagem="Tem certeza que deseja remover este visitante?" rotulo="Remover">
                <x-slot:gatilho><x-ui.button variante="perigo">Remover</x-ui.button></x-slot:gatilho>
            </x-ui.confirmation>
        </div>
    </form>
</x-layouts.admin>
