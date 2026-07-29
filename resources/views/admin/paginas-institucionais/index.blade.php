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
                    <td class="px-4 py-3 text-sm">
                        @can('cms.editar')
                            <a href="{{ route('admin.paginas-institucionais.edit', $pagina) }}" class="font-medium text-blue-800 hover:underline">Editar</a>

                            <x-ui.confirmation
                                :acao="route('admin.paginas-institucionais.destroy', $pagina)"
                                metodo="DELETE"
                                titulo="Remover página"
                                mensagem="Tem certeza que deseja remover esta página institucional?"
                                rotulo="Remover"
                            >
                                <x-slot:gatilho>
                                    <button type="button" class="ml-3 font-medium text-red-700 hover:underline">Remover</button>
                                </x-slot:gatilho>
                            </x-ui.confirmation>
                        @endcan
                    </td>
                </tr>
            @endforeach
        </x-ui.table>
    @endif
</x-layouts.admin>
