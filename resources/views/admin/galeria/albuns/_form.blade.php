@php($album ??= null)
<div class="space-y-4">
    <x-ui.input rotulo="Título" nome="titulo" :valor="$album->titulo ?? null" :erro="$errors->first('titulo')" obrigatorio />
    <x-ui.input rotulo="Slug" nome="slug" :valor="$album->slug ?? null" :erro="$errors->first('slug')" />
    <div class="grid gap-4 md:grid-cols-2">
        <x-ui.select rotulo="Status" nome="status" :opcoes="$statusDisponiveis->all()" :valor="$album->status->value ?? 'rascunho'" :erro="$errors->first('status')" obrigatorio />
        <x-ui.select rotulo="Visibilidade" nome="visibilidade" :opcoes="$visibilidades->all()" :valor="$album->visibilidade->value ?? 'restrita'" :erro="$errors->first('visibilidade')" obrigatorio />
    </div>
    <div>
        <label for="descricao" class="block text-sm font-medium text-gray-700">Descrição</label>
        <textarea id="descricao" name="descricao" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('descricao', $album->descricao ?? '') }}</textarea>
    </div>
    <div>
        <label for="fotografias" class="block text-sm font-medium text-gray-700">Fotografias</label>
        <input id="fotografias" name="fotografias[]" type="file" multiple accept="image/*" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm">
        <p class="mt-1 text-xs text-gray-500">Imagens JPG, PNG ou WebP. Máximo de 5 MB por foto.</p>
        @error('fotografias.*') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
</div>
