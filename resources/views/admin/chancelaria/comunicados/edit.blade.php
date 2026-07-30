<x-layouts.admin titulo="Editar Comunicado">
    <form method="POST" action="{{ route('admin.chancelaria.comunicados.update', $comunicado) }}" class="max-w-4xl space-y-6">
        @csrf
        @method('PUT')
        @include('admin.chancelaria.comunicados._form')
        <div class="flex gap-3">
            <x-ui.button tipo="submit">Salvar alterações</x-ui.button>
            <x-ui.confirmation :acao="route('admin.chancelaria.comunicados.destroy', $comunicado)" metodo="DELETE" titulo="Remover comunicado" mensagem="Tem certeza que deseja remover este comunicado?" rotulo="Remover">
                <x-slot:gatilho><x-ui.button variante="perigo">Remover</x-ui.button></x-slot:gatilho>
            </x-ui.confirmation>
        </div>
    </form>
</x-layouts.admin>
