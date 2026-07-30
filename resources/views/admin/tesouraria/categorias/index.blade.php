<x-layouts.admin titulo="Categorias Financeiras">
    <form method="POST" action="{{ route('admin.tesouraria.categorias.store') }}" class="mb-6 grid gap-4 rounded-md border border-gray-200 bg-white p-4 md:grid-cols-4">
        @csrf
        <x-ui.input rotulo="Nome" nome="nome" :erro="$errors->first('nome')" obrigatorio />
        <x-ui.select rotulo="Tipo" nome="tipo" :opcoes="$tipos->all()" :erro="$errors->first('tipo')" obrigatorio />
        <label class="mt-6 flex items-center gap-2 text-sm text-gray-700"><input type="checkbox" name="ativa" value="1" checked class="rounded border-gray-300 text-blue-800"> Ativa</label>
        <div class="mt-6"><x-ui.button tipo="submit">Cadastrar</x-ui.button></div>
    </form>
    <x-ui.table :cabecalhos="['Nome', 'Tipo', 'Status']">
        @foreach ($categorias as $categoria)
            <tr><td class="px-4 py-3">{{ $categoria->nome }}</td><td class="px-4 py-3">{{ $categoria->tipo->rotulo() }}</td><td class="px-4 py-3">{{ $categoria->ativa ? 'Ativa' : 'Inativa' }}</td></tr>
        @endforeach
    </x-ui.table>
</x-layouts.admin>
