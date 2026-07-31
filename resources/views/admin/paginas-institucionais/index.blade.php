<x-layouts.admin titulo="Páginas Institucionais">
    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-gray-600">Gerencie o conteúdo das páginas institucionais do site público.</p>

        @can('cms.editar')
            <a href="{{ route('admin.paginas-institucionais.create') }}">
                <x-ui.button>Nova página</x-ui.button>
            </a>
        @endcan
    </div>

    @if ($paginas->isEmpty())
        <x-ui.empty-state titulo="Nenhuma página cadastrada" />
    @else
        <x-ui.table :cabecalhos="['Título', 'Slug', 'Status', 'Ações']">
            @foreach ($paginas as $pagina)
                <tr>
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $pagina->titulo }}</td>
                    <td class="px-4 py-3 text-gray-600">/{{ $pagina->slug }}</td>
                    <td class="px-4 py-3">
                        <x-ui.badge :tipo="$pagina->publicado ? 'sucesso' : 'neutro'">{{ $pagina->publicado ? 'Publicado' : 'Rascunho' }}</x-ui.badge>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex flex-wrap items-center gap-2">
                            @can('cms.editar')
                                <x-ui.acao-botao :href="route('admin.paginas-institucionais.edit', $pagina)" icone="editar" cor="azul">Editar</x-ui.acao-botao>

                                <x-ui.confirmation
                                    :acao="route('admin.paginas-institucionais.destroy', $pagina)"
                                    metodo="DELETE"
                                    titulo="Remover página"
                                    mensagem="Tem certeza que deseja remover esta página institucional?"
                                    rotulo="Remover"
                                >
                                    <x-slot:gatilho>
                                        <x-ui.acao-botao icone="remover" cor="vermelho" tipo="button">Remover</x-ui.acao-botao>
                                    </x-slot:gatilho>
                                </x-ui.confirmation>
                            @endcan
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-ui.table>
    @endif
</x-layouts.admin>
