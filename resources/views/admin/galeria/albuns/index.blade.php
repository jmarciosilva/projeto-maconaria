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
                <td class="px-4 py-3 text-sm"><a href="{{ route('admin.galeria.albuns.show', $album) }}" class="font-medium text-blue-800 hover:underline">Abrir</a></td>
            </tr>
        @endforeach
    </x-ui.table>
    <div class="mt-4">{{ $albuns->links() }}</div>
</x-layouts.admin>
