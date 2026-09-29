<x-layouts.admin titulo="Sessões da Loja">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('admin.chancelaria.index') }}" class="text-sm font-medium text-blue-800 hover:underline">&larr; Voltar para a Chancelaria</a>
        @can('chancelaria.criar')
            <a href="{{ route('admin.chancelaria.sessoes.create') }}"><x-ui.button>Nova sessão</x-ui.button></a>
        @endcan
    </div>

    @if (session('erro'))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ session('erro') }}
        </div>
    @endif

    <section class="mb-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label for="ano" class="block text-sm font-medium text-gray-700">Ano</label>
                <select name="ano" id="ano" class="mt-1 rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Todos os anos</option>
                    @foreach ($anosDisponiveis as $anoDisponivel)
                        <option value="{{ $anoDisponivel }}" @selected($ano === $anoDisponivel)>{{ $anoDisponivel }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="classe" class="block text-sm font-medium text-gray-700">Classe</label>
                <select name="classe" id="classe" class="mt-1 rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Todas as classes</option>
                    @foreach ($classesDisponiveis as $valor => $rotulo)
                        <option value="{{ $valor }}" @selected($classe === $valor)>{{ $rotulo }}</option>
                    @endforeach
                </select>
            </div>
            <x-ui.button tipo="submit">Filtrar</x-ui.button>
            @if ($ano || $classe)
                <a href="{{ route('admin.chancelaria.sessoes.index') }}" class="text-sm text-gray-600 hover:underline">Limpar filtros</a>
            @endif
        </form>
    </section>

    @if ($sessoes->isEmpty())
        <x-ui.empty-state titulo="Nenhuma sessão encontrada" descricao="Cadastre a primeira sessão para começar o lançamento da frequência." />
    @else
        <x-ui.table :cabecalhos="['Data', 'Horário', 'Classe', 'Identificação', 'Frequência', 'Ações']">
            @foreach ($sessoes as $sessao)
                <tr>
                    <td class="px-4 py-3 text-gray-900">{{ $sessao->inicio_em->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $sessao->inicio_em->format('H:i') }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $sessao->sessao_classe?->rotulo() ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $sessao->titulo }}</td>
                    <td class="px-4 py-3">
                        @if ($sessao->frequencias_count > 0)
                            <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-800">
                                {{ $sessao->frequencias_count }} lançamento(s)
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">
                                Sem lançamento
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm">
                        <div class="flex flex-wrap gap-3">
                            @can('chancelaria.editar')
                                <a href="{{ route('admin.chancelaria.frequencias.edit', $sessao) }}" class="font-medium text-blue-800 hover:underline">Frequência</a>
                                <a href="{{ route('admin.chancelaria.sessoes.edit', $sessao) }}" class="font-medium text-gray-700 hover:underline">Editar</a>
                                @if ($sessao->frequencias_count === 0)
                                    <form method="POST" action="{{ route('admin.chancelaria.sessoes.destroy', $sessao) }}" onsubmit="return confirm('Remover esta sessão?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="font-medium text-red-700 hover:underline">Excluir</button>
                                    </form>
                                @endif
                            @endcan
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-ui.table>

        <div class="mt-4">
            {{ $sessoes->links() }}
        </div>
    @endif
</x-layouts.admin>
