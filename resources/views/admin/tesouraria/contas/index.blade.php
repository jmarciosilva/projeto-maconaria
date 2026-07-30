<x-layouts.admin titulo="Contas Financeiras">
    <form method="POST" action="{{ route('admin.tesouraria.contas.store') }}" class="mb-6 grid gap-4 rounded-md border border-gray-200 bg-white p-4 md:grid-cols-5">
        @csrf
        <x-ui.input rotulo="Nome" nome="nome" :erro="$errors->first('nome')" obrigatorio />
        <x-ui.input rotulo="Instituição" nome="instituicao" :erro="$errors->first('instituicao')" />
        <x-ui.select rotulo="Tipo" nome="tipo" :opcoes="$tipos->all()" :erro="$errors->first('tipo')" obrigatorio />
        <x-ui.input rotulo="Saldo inicial" nome="saldo_inicial" :erro="$errors->first('saldo_inicial_centavos')" />
        <div class="mt-6"><x-ui.button tipo="submit">Cadastrar</x-ui.button></div>
    </form>
    <x-ui.table :cabecalhos="['Nome', 'Instituição', 'Tipo', 'Saldo inicial']">
        @foreach ($contas as $conta)
            <tr><td class="px-4 py-3">{{ $conta->nome }}</td><td class="px-4 py-3">{{ $conta->instituicao ?? '—' }}</td><td class="px-4 py-3">{{ $conta->tipo->rotulo() }}</td><td class="px-4 py-3">R$ {{ \App\Support\Tesouraria\ConversorMoeda::formatar($conta->saldo_inicial_centavos) }}</td></tr>
        @endforeach
    </x-ui.table>
</x-layouts.admin>
