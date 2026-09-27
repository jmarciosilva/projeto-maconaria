<x-layouts.admin titulo="Editar Recado">
    <form method="POST" action="{{ route('admin.recados.update', $recado) }}" class="max-w-4xl space-y-6">
        @csrf
        @method('PUT')
        @include('admin.recados._form')
        <div class="flex gap-3">
            <x-ui.button tipo="submit">Salvar alterações</x-ui.button>
            <x-ui.confirmation :acao="route('admin.recados.destroy', $recado)" metodo="DELETE" titulo="Remover recado" mensagem="Tem certeza que deseja remover este recado?" rotulo="Remover">
                <x-slot:gatilho><x-ui.button variante="perigo">Remover</x-ui.button></x-slot:gatilho>
            </x-ui.confirmation>
        </div>
    </form>
</x-layouts.admin>
