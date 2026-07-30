<x-layouts.admin titulo="Fechamento Financeiro">
    <form method="POST" action="{{ route('admin.tesouraria.fechamentos.store') }}" class="mb-6 grid gap-4 rounded-md border border-gray-200 bg-white p-4 md:grid-cols-4">@csrf
        <x-ui.input rotulo="Ano" nome="ano" tipo="number" :valor="now()->year" obrigatorio /><x-ui.input rotulo="Mês" nome="mes" tipo="number" :valor="now()->month" obrigatorio /><x-ui.input rotulo="Observação" nome="observacao" /><div class="mt-6"><x-ui.button tipo="submit">Fechar</x-ui.button></div>
    </form>
    <x-ui.table :cabecalhos="['Período', 'Fechado em', 'Observação']">@foreach ($fechamentos as $f)<tr><td class="px-4 py-3">{{ str_pad((string) $f->mes, 2, '0', STR_PAD_LEFT) }}/{{ $f->ano }}</td><td class="px-4 py-3">{{ $f->fechado_em->format('d/m/Y H:i') }}</td><td class="px-4 py-3">{{ $f->observacao ?? '—' }}</td></tr>@endforeach</x-ui.table>
</x-layouts.admin>
