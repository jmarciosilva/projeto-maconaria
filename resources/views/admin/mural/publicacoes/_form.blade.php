@php($publicacao ??= null)
<div class="space-y-4">
    <x-ui.input rotulo="Título" nome="titulo" :valor="$publicacao->titulo ?? null" :erro="$errors->first('titulo')" obrigatorio />
    <div class="grid gap-4 md:grid-cols-2">
        <x-ui.select rotulo="Status" nome="status" :opcoes="$statusDisponiveis->all()" :valor="$publicacao->status->value ?? 'rascunho'" :erro="$errors->first('status')" obrigatorio />
        <x-ui.select rotulo="Visibilidade" nome="visibilidade" :opcoes="$visibilidades->all()" :valor="$publicacao->visibilidade->value ?? 'restrita'" :erro="$errors->first('visibilidade')" obrigatorio />
    </div>
    <div>
        <label for="conteudo" class="block text-sm font-medium text-gray-700">Conteúdo</label>
        <textarea id="conteudo" name="conteudo" rows="6" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>{{ old('conteudo', $publicacao->conteudo ?? '') }}</textarea>
    </div>
</div>
