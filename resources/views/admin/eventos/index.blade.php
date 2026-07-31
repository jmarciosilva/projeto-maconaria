<x-layouts.admin titulo="Eventos">
    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-gray-600">Gerencie eventos, sessões, visibilidade e confirmações de presença.</p>

        @can('eventos.criar')
            <a href="{{ route('admin.eventos.create') }}">
                <x-ui.button>Novo evento</x-ui.button>
            </a>
        @endcan
    </div>

    @if ($eventos->isEmpty())
        <x-ui.empty-state titulo="Nenhum evento cadastrado" descricao="Cadastre o primeiro evento ou sessão da agenda." />
    @else
        <x-ui.table :cabecalhos="['Título', 'Tipo', 'Data', 'Status', 'Visibilidade', 'Presenças', 'Ações']">
            @foreach ($eventos as $evento)
                <tr>
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-900">{{ $evento->titulo }}</p>
                        <p class="text-xs text-gray-500">{{ $evento->slug }}</p>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $evento->tipo->rotulo() }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $evento->inicio_em->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-3">
                        <x-ui.badge :tipo="$evento->status->value === 'publicado' ? 'sucesso' : ($evento->status->value === 'cancelado' ? 'erro' : 'neutro')">{{ $evento->status->rotulo() }}</x-ui.badge>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $evento->visibilidade->rotulo() }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $evento->confirmacoes_ativas_count }}{{ $evento->capacidade ? ' / '.$evento->capacidade : '' }}</td>
                    <td class="px-4 py-3">
                        <div class="flex flex-wrap items-center gap-2">
                            @can('eventos.editar')
                                <x-ui.acao-botao :href="route('admin.eventos.edit', $evento)" icone="editar" cor="azul">Editar</x-ui.acao-botao>
                            @endcan

                            @can('eventos.excluir')
                                <x-ui.confirmation :acao="route('admin.eventos.destroy', $evento)" metodo="DELETE" titulo="Remover evento" mensagem="Tem certeza que deseja remover este evento?" rotulo="Remover">
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

        <div class="mt-4">{{ $eventos->links() }}</div>
    @endif
</x-layouts.admin>
