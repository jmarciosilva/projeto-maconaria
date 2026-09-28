<x-layouts.admin titulo="Notícias">
    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <p class="text-sm text-gray-600">Gerencie notícias, rascunhos, agendamentos e publicações.</p>
            @if (!$noticias->isEmpty())
                <p class="mt-1 text-xs text-gray-500">{{ $noticias->total() }} notícia{{ $noticias->total() !== 1 ? 's' : '' }} cadastrada{{ $noticias->total() !== 1 ? 's' : '' }}</p>
            @endif
        </div>

        @can('noticias.criar')
            <a href="{{ route('admin.noticias.create') }}" class="inline-flex">
                <x-ui.button>
                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Nova notícia
                </x-ui.button>
            </a>
        @endcan
    </div>

    @if ($noticias->isEmpty())
        <x-ui.empty-state
            titulo="Nenhuma notícia cadastrada"
            descricao="Cadastre a primeira notícia da Loja para começar."
        />
    @else
        <div class="overflow-x-auto rounded-lg border border-gray-200">
            <x-ui.table :cabecalhos="['Título', 'Categoria', 'Status', 'Visibilidade', 'Publicação', 'Ações']">
                @foreach ($noticias as $noticia)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900">{{ $noticia->titulo }}</p>
                            <p class="text-xs text-gray-500">{{ $noticia->slug }}</p>
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            @if ($noticia->categoria)
                                <span class="inline-flex items-center rounded-md bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700">
                                    {{ $noticia->categoria->nome }}
                                </span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $statusMap = [
                                    'rascunho' => ['tipo' => 'neutro', 'icone' => '✎'],
                                    'publicada' => ['tipo' => 'sucesso', 'icone' => '✓'],
                                    'agendada' => ['tipo' => 'aviso', 'icone' => '⏱'],
                                ];
                                $statusInfo = $statusMap[$noticia->status->value] ?? ['tipo' => 'neutro', 'icone' => '—'];
                            @endphp
                            <x-ui.badge :tipo="$statusInfo['tipo']">
                                <span class="mr-1">{{ $statusInfo['icone'] }}</span>{{ $noticia->status->rotulo() }}
                            </x-ui.badge>
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            <span class="text-xs">{{ $noticia->visibilidade->rotulo() }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            @if ($noticia->publicado_em)
                                <span class="text-xs">{{ $noticia->publicado_em->format('d/m/Y') }}</span>
                                <span class="text-gray-400">{{ $noticia->publicado_em->format('H:i') }}</span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap items-center gap-2">
                                @can('noticias.editar')
                                    <a href="{{ route('admin.noticias.edit', $noticia) }}" class="inline-flex">
                                        <x-ui.acao-botao icone="editar" cor="azul">Editar</x-ui.acao-botao>
                                    </a>
                                @endcan

                                @can('noticias.excluir')
                                    <x-ui.confirmation
                                        :acao="route('admin.noticias.destroy', $noticia)"
                                        metodo="DELETE"
                                        titulo="Remover notícia"
                                        mensagem="Tem certeza que deseja remover esta notícia? Esta ação não pode ser desfeita."
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
        </div>

        <div class="mt-6">{{ $noticias->links() }}</div>
    @endif
</x-layouts.admin>
