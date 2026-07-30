@php($lancamento ??= null)
<div class="space-y-4">
    <div class="grid gap-4 md:grid-cols-2">
        <x-ui.select rotulo="Tipo" nome="tipo" :opcoes="$tipos->all()" :valor="$lancamento->tipo->value ?? 'receita'" :erro="$errors->first('tipo')" obrigatorio />
        <x-ui.select rotulo="Status" nome="status" :opcoes="$statusDisponiveis" :valor="$lancamento->status->value ?? 'pendente'" :erro="$errors->first('status')" obrigatorio />
    </div>
    <x-ui.input rotulo="Descrição" nome="descricao" :valor="$lancamento->descricao ?? null" :erro="$errors->first('descricao')" obrigatorio />
    <div class="grid gap-4 md:grid-cols-3">
        <x-ui.select rotulo="Categoria" nome="categoria_id" :opcoes="$categorias->all()" :valor="$lancamento->categoria_id ?? null" :erro="$errors->first('categoria_id')" obrigatorio />
        <x-ui.select rotulo="Conta" nome="conta_id" :opcoes="$contas->all()" :valor="$lancamento->conta_id ?? null" :erro="$errors->first('conta_id')" obrigatorio />
        <x-ui.select rotulo="Irmão" nome="irmao_id" :opcoes="['' => 'Sem vínculo'] + $irmaos->all()" :valor="$lancamento->irmao_id ?? null" :erro="$errors->first('irmao_id')" />
    </div>
    <div class="grid gap-4 md:grid-cols-3">
        <x-ui.input rotulo="Valor" nome="valor" :valor="isset($lancamento) ? \App\Support\Tesouraria\ConversorMoeda::formatar($lancamento->valor_centavos) : null" :erro="$errors->first('valor_centavos')" obrigatorio />
        <x-ui.input rotulo="Competência" nome="data_competencia" tipo="date" :valor="isset($lancamento) ? $lancamento->data_competencia->format('Y-m-d') : now()->format('Y-m-d')" :erro="$errors->first('data_competencia')" obrigatorio />
        <x-ui.input rotulo="Vencimento" nome="data_vencimento" tipo="date" :valor="isset($lancamento?->data_vencimento) ? $lancamento->data_vencimento->format('Y-m-d') : null" :erro="$errors->first('data_vencimento')" />
    </div>
    <div><label class="block text-sm font-medium text-gray-700">Observação</label><textarea name="observacao" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('observacao', $lancamento->observacao ?? '') }}</textarea></div>
</div>
