<x-layouts.admin titulo="Mural">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-gray-600">Publicações, comentários, reações e moderação.</p>
        @can('mural.criar')
            <a href="{{ route('admin.mural.publicacoes.create') }}"><x-ui.button>Nova publicação</x-ui.button></a>
        @endcan
    </div>
    <x-ui.table :cabecalhos="['Título', 'Status', 'Visibilidade', 'Comentários', 'Reações', 'Ações']">
        @foreach ($publicacoes as $publicacao)
            <tr>
                <td class="px-4 py-3 font-medium text-gray-900">{{ $publicacao->titulo }}</td>
                <td class="px-4 py-3"><x-ui.badge>{{ $publicacao->status->rotulo() }}</x-ui.badge></td>
                <td class="px-4 py-3 text-gray-600">{{ $publicacao->visibilidade->rotulo() }}</td>
                <td class="px-4 py-3 text-gray-600">{{ $publicacao->comentarios_count }}</td>
                <td class="px-4 py-3 text-gray-600">{{ $publicacao->reacoes_count }}</td>
                <td class="px-4 py-3 text-sm"><a href="{{ route('admin.mural.publicacoes.show', $publicacao) }}" class="font-medium text-blue-800 hover:underline">Abrir</a></td>
            </tr>
        @endforeach
    </x-ui.table>
    <div class="mt-4">{{ $publicacoes->links() }}</div>
</x-layouts.admin>
