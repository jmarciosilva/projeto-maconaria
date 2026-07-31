<x-layouts.admin titulo="Tags de Notícias">
    @can('noticias.editar')
        <form method="POST" action="{{ route('admin.noticia-tags.store') }}" class="mb-6 grid gap-4 rounded-md border border-gray-200 bg-white p-4 md:grid-cols-3">
            @csrf
            <x-ui.input rotulo="Nome" nome="nome" :erro="$errors->first('nome')" obrigatorio />
            <x-ui.input rotulo="Slug" nome="slug" :erro="$errors->first('slug')" placeholder="gerado automaticamente" />
            <div class="mt-6">
                <x-ui.button tipo="submit">Cadastrar tag</x-ui.button>
            </div>
        </form>
    @endcan

    <x-ui.table :cabecalhos="['Nome', 'Slug', 'Notícias', 'Ações']">
        @foreach ($tags as $tag)
            <tr>
                <td class="px-4 py-3 font-medium text-gray-900">{{ $tag->nome }}</td>
                <td class="px-4 py-3 text-gray-600">{{ $tag->slug }}</td>
                <td class="px-4 py-3 text-gray-600">{{ $tag->noticias_count }}</td>
                <td class="px-4 py-3">
                    @can('noticias.excluir')
                        <x-ui.confirmation :acao="route('admin.noticia-tags.destroy', $tag)" metodo="DELETE" titulo="Remover tag" mensagem="Tem certeza que deseja remover esta tag?" rotulo="Remover">
                            <x-slot:gatilho>
                                <x-ui.acao-botao icone="remover" cor="vermelho" tipo="button">Remover</x-ui.acao-botao>
                            </x-slot:gatilho>
                        </x-ui.confirmation>
                    @endcan
                </td>
            </tr>
        @endforeach
    </x-ui.table>
</x-layouts.admin>
