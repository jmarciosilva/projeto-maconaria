<x-layouts.admin titulo="Documentos e Trabalhos">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-gray-600">Atividades, entregas, avaliações, comentários e arquivos privados.</p>

        @can('documentos.avaliar')
            <a href="{{ route('admin.documentos.atividades.create') }}">
                <x-ui.button>Nova atividade</x-ui.button>
            </a>
        @endcan
    </div>

    <x-ui.table :cabecalhos="['Título', 'Status', 'Prazo', 'Entregas', 'Ações']">
        @foreach ($atividades as $atividade)
            <tr>
                <td class="px-4 py-3 font-medium text-gray-900">{{ $atividade->titulo }}</td>
                <td class="px-4 py-3"><x-ui.badge>{{ $atividade->status->rotulo() }}</x-ui.badge></td>
                <td class="px-4 py-3 text-gray-600">{{ $atividade->prazo_entrega_em?->format('d/m/Y H:i') ?? 'Sem prazo' }}</td>
                <td class="px-4 py-3 text-gray-600">{{ $atividade->entregas_count }}</td>
                <td class="px-4 py-3">
                    <x-ui.acao-botao :href="route('admin.documentos.atividades.show', $atividade)" icone="ver" cor="cinza">Abrir</x-ui.acao-botao>
                </td>
            </tr>
        @endforeach
    </x-ui.table>

    <div class="mt-4">{{ $atividades->links() }}</div>
</x-layouts.admin>
