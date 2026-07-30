@php($visitante ??= null)
<div class="space-y-4">
    <x-ui.select rotulo="Evento" nome="evento_id" :opcoes="['' => 'Sem evento vinculado'] + $eventos->all()" :valor="$visitante->evento_id ?? null" :erro="$errors->first('evento_id')" />
    <x-ui.input rotulo="Nome" nome="nome" :valor="$visitante->nome ?? null" :erro="$errors->first('nome')" obrigatorio />
    <div class="grid gap-4 md:grid-cols-2">
        <x-ui.input rotulo="Loja de origem" nome="loja_origem" :valor="$visitante->loja_origem ?? null" :erro="$errors->first('loja_origem')" />
        <x-ui.input rotulo="Potência" nome="potencia" :valor="$visitante->potencia ?? null" :erro="$errors->first('potencia')" />
    </div>
    <x-ui.input rotulo="Documento" nome="documento" :valor="$visitante->documento ?? null" :erro="$errors->first('documento')" />
    <div>
        <label for="observacao" class="block text-sm font-medium text-gray-700">Observação</label>
        <textarea id="observacao" name="observacao" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">{{ old('observacao', $visitante->observacao ?? '') }}</textarea>
    </div>
</div>
