<x-layouts.admin titulo="Categorias de Notícias">
    @can('noticias.editar')
        <form method="POST" action="{{ route('admin.noticia-categorias.store') }}" class="mb-6 grid gap-4 rounded-md border border-gray-200 bg-white p-4 md:grid-cols-4">
            @csrf
            <x-ui.input rotulo="Nome" nome="nome" :erro="$errors->first('nome')" obrigatorio />
            <x-ui.input rotulo="Slug" nome="slug" :erro="$errors->first('slug')" placeholder="gerado automaticamente" />
            <x-ui.input rotulo="Descrição" nome="descricao" :erro="$errors->first('descricao')" />
            <label class="mt-6 flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="ativa" value="1" checked class="rounded border-gray-300 text-blue-800 focus:ring-blue-700">
                Ativa
            </label>
            <div class="md:col-span-4">
                <x-ui.button tipo="submit">Cadastrar categoria</x-ui.button>
            </div>
        </form>
    @endcan

    <x-ui.table :cabecalhos="['Nome', 'Slug', 'Notícias', 'Status', 'Ações']">
        @foreach ($categorias as $categoria)
            <tr>
                <td class="px-4 py-3 font-medium text-gray-900">{{ $categoria->nome }}</td>
                <td class="px-4 py-3 text-gray-600">{{ $categoria->slug }}</td>
                <td class="px-4 py-3 text-gray-600">{{ $categoria->noticias_count }}</td>
                <td class="px-4 py-3">
                    <x-ui.badge :tipo="$categoria->ativa ? 'sucesso' : 'neutro'">{{ $categoria->ativa ? 'Ativa' : 'Inativa' }}</x-ui.badge>
                </td>
                <td class="px-4 py-3 text-sm">
                    @can('noticias.excluir')
                        <x-ui.confirmation :acao="route('admin.noticia-categorias.destroy', $categoria)" metodo="DELETE" titulo="Remover categoria" mensagem="Tem certeza que deseja remover esta categoria?" rotulo="Remover">
                            <x-slot:gatilho>
                                <button type="button" class="font-medium text-red-700 hover:underline">Remover</button>
                            </x-slot:gatilho>
                        </x-ui.confirmation>
                    @endcan
                </td>
            </tr>
        @endforeach
    </x-ui.table>
</x-layouts.admin>
