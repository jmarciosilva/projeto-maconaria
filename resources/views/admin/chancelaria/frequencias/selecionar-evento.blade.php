<x-layouts.admin titulo="Registrar Frequência">
    <div class="mb-4 flex justify-end">
        <a href="{{ route('admin.chancelaria.relatorios.frequencia') }}" class="text-sm font-semibold text-blue-800 hover:underline">
            Relatório de frequência &rarr;
        </a>
    </div>

    @can('chancelaria.criar')
        <section class="mb-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-base font-semibold text-gray-900">Registrar sessão passada</h2>
            <p class="mt-0.5 text-sm text-gray-500">Cadastre rapidamente uma sessão já realizada, só para lançar a presença dos Irmãos nela. Para eventos da agenda pública do site, use o módulo Eventos.</p>

            <form method="POST" action="{{ route('admin.chancelaria.frequencias.armazenar-sessao') }}" class="mt-4 grid gap-4 sm:grid-cols-4">
                @csrf
                <x-ui.input rotulo="Data da sessão" nome="inicio_em" tipo="datetime-local" :erro="$errors->first('inicio_em')" obrigatorio />
                <x-ui.select rotulo="Classe" nome="sessao_classe" :opcoes="['' => 'Não informada'] + $classesDisponiveis->all()" :erro="$errors->first('sessao_classe')" />
                <x-ui.input rotulo="Título (opcional)" nome="titulo" :erro="$errors->first('titulo')" placeholder="Ex.: Sessão ordinária" />
                <x-ui.input rotulo="Local (opcional)" nome="local" :erro="$errors->first('local')" />

                <div class="sm:col-span-4">
                    <x-ui.button tipo="submit">Registrar sessão e lançar presença</x-ui.button>
                </div>
            </form>
        </section>
    @endcan

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
            <x-ui.button tipo="submit">Filtrar</x-ui.button>
            @if ($ano)
                <a href="{{ route('admin.chancelaria.frequencias.selecionar-evento') }}" class="text-sm text-gray-600 hover:underline">Limpar filtro</a>
            @endif
        </form>
    </section>

    @if ($eventos->isEmpty())
        <x-ui.empty-state titulo="Nenhuma sessão encontrada" descricao="Registre uma sessão acima para começar a lançar frequência." />
    @else
        <x-ui.table :cabecalhos="['Sessão', 'Classe', 'Data', 'Ações']">
            @foreach ($eventos as $evento)
                <tr>
                    <td class="px-4 py-3 text-gray-900">{{ $evento->titulo }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $evento->sessao_classe?->rotulo() ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $evento->inicio_em->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-3 text-sm">
                        <a href="{{ route('admin.chancelaria.frequencias.edit', $evento) }}" class="font-medium text-blue-800 hover:underline">Registrar</a>
                    </td>
                </tr>
            @endforeach
        </x-ui.table>

        <div class="mt-4">
            {{ $eventos->links() }}
        </div>
    @endif
</x-layouts.admin>
