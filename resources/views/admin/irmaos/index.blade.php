<x-layouts.admin titulo="Irmãos">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <x-ui.input rotulo="Nome" nome="nome" :valor="request('nome')" />

            <x-ui.select
                rotulo="Situação"
                nome="situacao"
                :opcoes="collect(['' => 'Todas'])->union(collect(\App\Enums\SituacaoCadastralIrmao::cases())->mapWithKeys(fn ($c) => [$c->value => $c->rotulo()]))->all()"
                :valor="request('situacao')"
            />

            <x-ui.select
                rotulo="Grau"
                nome="grau"
                :opcoes="collect(['' => 'Todos'])->union(collect(\App\Enums\GrauMaconico::cases())->mapWithKeys(fn ($c) => [$c->value => $c->rotulo()]))->all()"
                :valor="request('grau')"
            />

            <x-ui.button tipo="submit" variante="secundario">Filtrar</x-ui.button>
        </form>

        @can('irmaos.criar')
            <a href="{{ route('admin.irmaos.create') }}">
                <x-ui.button>Novo Irmão</x-ui.button>
            </a>
        @endcan
    </div>

    @if ($irmaos->isEmpty())
        <x-ui.empty-state titulo="Nenhum Irmão cadastrado" descricao="Cadastre o primeiro Irmão para começar." />
    @else
        <x-ui.table :cabecalhos="['Nome', 'CIM', 'Grau', 'Situação', 'Cargo atual', 'Ações']">
            @foreach ($irmaos as $irmao)
                <tr>
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $irmao->nome_completo }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $irmao->cim ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $irmao->grau_atual?->rotulo() ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <x-ui.badge :tipo="$irmao->situacao_cadastral->value === 'ativo' ? 'sucesso' : 'neutro'">
                            {{ $irmao->situacao_cadastral->rotulo() }}
                        </x-ui.badge>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $irmao->cargo_atual ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm">
                        <a href="{{ route('admin.irmaos.show', $irmao) }}" class="font-medium text-blue-800 hover:underline">Ver</a>

                        @can('irmaos.editar')
                            <a href="{{ route('admin.irmaos.edit', $irmao) }}" class="ml-3 font-medium text-blue-800 hover:underline">Editar</a>
                        @endcan

                        @can('irmaos.excluir')
                            <span class="ml-3 inline">
                                <x-ui.confirmation
                                    :acao="route('admin.irmaos.destroy', $irmao)"
                                    metodo="DELETE"
                                    titulo="Remover Irmão"
                                    :mensagem="'Tem certeza que deseja remover '.$irmao->nome_completo.'? O registro poderá ser recuperado pelo banco de dados, mas deixará de aparecer no sistema.'"
                                    rotulo="Remover"
                                >
                                    <x-slot:gatilho>
                                        <button type="button" class="font-medium text-red-700 hover:underline">Remover</button>
                                    </x-slot:gatilho>
                                </x-ui.confirmation>
                            </span>
                        @endcan
                    </td>
                </tr>
            @endforeach
        </x-ui.table>

        <div class="mt-4">
            {{ $irmaos->links() }}
        </div>
    @endif
</x-layouts.admin>
