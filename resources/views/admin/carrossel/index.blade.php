<x-layouts.admin titulo="Carrossel da Página Inicial">
    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-gray-600">Gerencie as imagens exibidas no carrossel da página inicial.</p>

        @can('cms.editar')
            <a href="{{ route('admin.carrossel.create') }}">
                <x-ui.button>Novo item</x-ui.button>
            </a>
        @endcan
    </div>

    @if ($itens->isEmpty())
        <x-ui.empty-state titulo="Nenhum item cadastrado" descricao="Cadastre a primeira imagem do carrossel." />
    @else
        <x-ui.table :cabecalhos="['Imagem', 'Título', 'Ordem', 'Período', 'Status', 'Ações']">
            @foreach ($itens as $item)
                <tr>
                    <td class="px-4 py-3">
                        <img src="{{ asset('storage/'.$item->imagem_desktop) }}" alt="{{ $item->texto_alternativo }}" class="h-12 w-20 rounded object-cover">
                    </td>
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $item->titulo ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $item->ordem }}</td>
                    <td class="px-4 py-3 text-gray-600">
                        {{ optional($item->data_inicio)->format('d/m/Y') ?? 'Sem início' }}
                        —
                        {{ optional($item->data_fim)->format('d/m/Y') ?? 'Sem fim' }}
                    </td>
                    <td class="px-4 py-3">
                        <x-ui.badge :tipo="$item->ativo ? 'sucesso' : 'neutro'">{{ $item->ativo ? 'Ativo' : 'Inativo' }}</x-ui.badge>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex flex-wrap items-center gap-2">
                            @can('cms.editar')
                                <x-ui.acao-botao :href="route('admin.carrossel.edit', $item)" icone="editar" cor="azul">Editar</x-ui.acao-botao>

                                <x-ui.confirmation
                                    :acao="route('admin.carrossel.destroy', $item)"
                                    metodo="DELETE"
                                    titulo="Remover item do carrossel"
                                    mensagem="Tem certeza que deseja remover este item do carrossel?"
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
