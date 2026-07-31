<x-layouts.admin titulo="Lançamentos Financeiros">
    <div class="mb-4 flex justify-between"><p class="text-sm text-gray-600">Receitas, despesas, pagamentos e recebimentos.</p><a href="{{ route('admin.tesouraria.lancamentos.create') }}"><x-ui.button>Novo lançamento</x-ui.button></a></div>
    <x-ui.table :cabecalhos="['Descrição', 'Tipo', 'Valor', 'Status', 'Competência', 'Ações']">
        @foreach ($lancamentos as $lancamento)
            <tr>
                <td class="px-4 py-3">{{ $lancamento->descricao }}</td>
                <td class="px-4 py-3">{{ $lancamento->tipo->rotulo() }}</td>
                <td class="px-4 py-3">R$ {{ \App\Support\Tesouraria\ConversorMoeda::formatar($lancamento->valor_centavos) }}</td>
                <td class="px-4 py-3"><x-ui.badge :tipo="$lancamento->status->value === 'baixado' ? 'sucesso' : 'neutro'">{{ $lancamento->status->rotulo() }}</x-ui.badge></td>
                <td class="px-4 py-3">{{ $lancamento->data_competencia->format('d/m/Y') }}</td>
                <td class="px-4 py-3"><x-ui.acao-botao :href="route('admin.tesouraria.lancamentos.edit', $lancamento)" icone="editar" cor="azul">Editar</x-ui.acao-botao></td>
            </tr>
        @endforeach
    </x-ui.table>
    <div class="mt-4">{{ $lancamentos->links() }}</div>
</x-layouts.admin>
