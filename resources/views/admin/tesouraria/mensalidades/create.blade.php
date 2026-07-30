<x-layouts.admin titulo="Gerar Mensalidades">
    <form method="POST" action="{{ route('admin.tesouraria.mensalidades.store') }}" class="max-w-3xl space-y-4">@csrf
        <x-ui.select rotulo="Categoria" nome="categoria_id" :opcoes="$categorias->all()" obrigatorio />
        <x-ui.select rotulo="Conta" nome="conta_id" :opcoes="$contas->all()" obrigatorio />
        <div class="grid gap-4 md:grid-cols-3"><x-ui.input rotulo="Valor" nome="valor" obrigatorio /><x-ui.input rotulo="Ano" nome="ano" tipo="number" :valor="now()->year" obrigatorio /><x-ui.input rotulo="Mês" nome="mes" tipo="number" :valor="now()->month" obrigatorio /></div>
        <x-ui.button tipo="submit">Gerar mensalidades</x-ui.button>
    </form>
</x-layouts.admin>
