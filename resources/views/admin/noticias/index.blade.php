<x-layouts.admin titulo="Notícias">
    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-gray-600">Gerencie notícias, rascunhos, agendamentos e publicações.</p>

        @can('noticias.criar')
            <a href="{{ route('admin.noticias.create') }}">
                <x-ui.button>Nova notícia</x-ui.button>
            </a>
        @endcan
    </div>

    @if ($noticias->isEmpty())
        <x-ui.empty-state titulo="Nenhuma notícia cadastrada" descricao="Cadastre a primeira notícia da Loja." />
    @else
        <x-ui.table :cabecalhos="['Título', 'Categoria', 'Status', 'Visibilidade', 'Publicação', 'Ações']">
            @foreach ($noticias as $noticia)
                <tr>
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-900">{{ $noticia->titulo }}</p>
                        <p class="text-xs text-gray-500">{{ $noticia->slug }}</p>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $noticia->categoria->nome ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <x-ui.badge :tipo="$noticia->status->value === 'publicada' ? 'sucesso' : 'neutro'">{{ $noticia->status->rotulo() }}</x-ui.badge>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $noticia->visibilidade->rotulo() }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ optional($noticia->publicado_em)->format('d/m/Y H:i') ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <div class="flex flex-wrap items-center gap-2">
                            @can('noticias.editar')
                                <x-ui.acao-botao :href="route('admin.noticias.edit', $noticia)" icone="editar" cor="azul">Editar</x-ui.acao-botao>
                            @endcan

                            @can('noticias.excluir')
                                <x-ui.confirmation
                                    :acao="route('admin.noticias.destroy', $noticia)"
                                    metodo="DELETE"
                                    titulo="Remover notícia"
                                    mensagem="Tem certeza que deseja remover esta notícia?"
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

        <div class="mt-4">{{ $noticias->links() }}</div>
    @endif
</x-layouts.admin>
