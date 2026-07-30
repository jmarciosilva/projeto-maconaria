<x-layouts.admin titulo="Secretaria">
    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-gray-600">Gerencie atas, correspondências, documentos oficiais, aprovação e numeração.</p>

        @can('secretaria.criar-ata')
            <a href="{{ route('admin.secretaria.documentos.create') }}">
                <x-ui.button>Novo documento</x-ui.button>
            </a>
        @endcan
    </div>

    @if ($documentos->isEmpty())
        <x-ui.empty-state titulo="Nenhum documento cadastrado" descricao="Cadastre a primeira ata, correspondência ou documento oficial." />
    @else
        <x-ui.table :cabecalhos="['Código', 'Tipo', 'Título', 'Status', 'Data', 'Ações']">
            @foreach ($documentos as $documento)
                <tr>
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $documento->codigo }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $documento->tipo->rotulo() }}</td>
                    <td class="px-4 py-3 text-gray-900">{{ $documento->titulo }}</td>
                    <td class="px-4 py-3">
                        <x-ui.badge :tipo="$documento->status->value === 'publicado' ? 'sucesso' : ($documento->status->value === 'aprovado' ? 'aviso' : 'neutro')">{{ $documento->status->rotulo() }}</x-ui.badge>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ optional($documento->data_documento)->format('d/m/Y') ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm">
                        @can('secretaria.editar-ata')
                            <a href="{{ route('admin.secretaria.documentos.edit', $documento) }}" class="font-medium text-blue-800 hover:underline">Editar</a>
                        @endcan
                    </td>
                </tr>
            @endforeach
        </x-ui.table>

        <div class="mt-4">{{ $documentos->links() }}</div>
    @endif
</x-layouts.admin>
