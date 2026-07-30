<x-layouts.admin titulo="Comunicados da Chancelaria">
    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-gray-600">Gerencie comunicados internos da Chancelaria.</p>
        <a href="{{ route('admin.chancelaria.comunicados.create') }}"><x-ui.button>Novo comunicado</x-ui.button></a>
    </div>

    <x-ui.table :cabecalhos="['Título', 'Status', 'Publicado em', 'Ações']">
        @foreach ($comunicados as $comunicado)
            <tr>
                <td class="px-4 py-3 font-medium text-gray-900">{{ $comunicado->titulo }}</td>
                <td class="px-4 py-3"><x-ui.badge :tipo="$comunicado->status->value === 'publicado' ? 'sucesso' : 'neutro'">{{ $comunicado->status->rotulo() }}</x-ui.badge></td>
                <td class="px-4 py-3 text-gray-600">{{ optional($comunicado->publicado_em)->format('d/m/Y H:i') ?? '—' }}</td>
                <td class="px-4 py-3 text-sm">
                    <a href="{{ route('admin.chancelaria.comunicados.edit', $comunicado) }}" class="font-medium text-blue-800 hover:underline">Editar</a>
                </td>
            </tr>
        @endforeach
    </x-ui.table>

    <div class="mt-4">{{ $comunicados->links() }}</div>
</x-layouts.admin>
