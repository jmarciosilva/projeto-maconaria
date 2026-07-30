@php($saldo = $receitas - $despesas)
<x-layouts.admin titulo="Tesouraria">
    <div class="mb-6 grid gap-4 md:grid-cols-4">
        <div class="rounded-md border border-gray-200 bg-white p-4 shadow-sm"><p class="text-sm text-gray-600">Receitas baixadas</p><p class="text-2xl font-bold text-green-700">R$ {{ \App\Support\Tesouraria\ConversorMoeda::formatar($receitas) }}</p></div>
        <div class="rounded-md border border-gray-200 bg-white p-4 shadow-sm"><p class="text-sm text-gray-600">Despesas baixadas</p><p class="text-2xl font-bold text-red-700">R$ {{ \App\Support\Tesouraria\ConversorMoeda::formatar($despesas) }}</p></div>
        <div class="rounded-md border border-gray-200 bg-white p-4 shadow-sm"><p class="text-sm text-gray-600">Saldo</p><p class="text-2xl font-bold text-blue-800">R$ {{ \App\Support\Tesouraria\ConversorMoeda::formatar($saldo) }}</p></div>
        <div class="rounded-md border border-gray-200 bg-white p-4 shadow-sm"><p class="text-sm text-gray-600">Pendentes</p><p class="text-2xl font-bold text-amber-700">{{ $pendentes }}</p></div>
    </div>
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('admin.tesouraria.lancamentos.index') }}"><x-ui.button>Lançamentos</x-ui.button></a>
        <a href="{{ route('admin.tesouraria.categorias.index') }}"><x-ui.button variante="secundario">Categorias</x-ui.button></a>
        <a href="{{ route('admin.tesouraria.contas.index') }}"><x-ui.button variante="secundario">Contas</x-ui.button></a>
        <a href="{{ route('admin.tesouraria.mensalidades.create') }}"><x-ui.button variante="secundario">Mensalidades</x-ui.button></a>
        <a href="{{ route('admin.tesouraria.fechamentos.index') }}"><x-ui.button variante="secundario">Fechamentos</x-ui.button></a>
    </div>
</x-layouts.admin>
