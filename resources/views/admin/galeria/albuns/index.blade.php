<x-layouts.admin titulo="Galeria">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-gray-600">Álbuns, fotografias, moderação e visibilidade.</p>
        @can('galeria.criar')
            <a href="{{ route('admin.galeria.albuns.create') }}"><x-ui.button>Novo álbum</x-ui.button></a>
        @endcan
    </div>

    <x-ui.table :cabecalhos="['Título', 'Status', 'Visibilidade', 'Fotos', 'Ações']">
        @foreach ($albuns as $album)
            <tr>
                <td class="px-4 py-3 font-medium text-gray-900">{{ $album->titulo }}</td>
                <td class="px-4 py-3"><x-ui.badge>{{ $album->status->rotulo() }}</x-ui.badge></td>
                <td class="px-4 py-3 text-gray-600">{{ $album->visibilidade->rotulo() }}</td>
                <td class="px-4 py-3 text-gray-600">{{ $album->fotografias_count }}</td>
                <td class="px-4 py-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <x-ui.acao-botao :href="route('admin.galeria.albuns.show', $album)" icone="ver" cor="cinza">Abrir</x-ui.acao-botao>

                        @can('galeria.excluir')
                            <x-ui.confirmation :acao="route('admin.galeria.albuns.destroy', $album)" metodo="DELETE" titulo="Remover álbum" :mensagem="'Tem certeza que deseja remover o álbum \''.$album->titulo.'\'? As fotografias também serão removidas.'" rotulo="Remover">
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
    <div class="mt-4">{{ $albuns->links() }}</div>
</x-layouts.admin>
