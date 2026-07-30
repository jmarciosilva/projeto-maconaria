@php($comunicado ??= null)
<div class="space-y-4">
    <x-ui.input rotulo="Título" nome="titulo" :valor="$comunicado->titulo ?? null" :erro="$errors->first('titulo')" obrigatorio />
    <x-ui.select rotulo="Status" nome="status" :opcoes="$statusDisponiveis->all()" :valor="$comunicado->status->value ?? 'rascunho'" :erro="$errors->first('status')" obrigatorio />

    <div>
        <label for="conteudo-editor" class="block text-sm font-medium text-gray-700">Conteúdo</label>
        <div id="conteudo-editor" data-quill-editor data-quill-target="conteudo-input" class="mt-1 bg-white">{!! old('conteudo', $comunicado->conteudo ?? '') !!}</div>
        <textarea name="conteudo" id="conteudo-input" class="hidden">{{ old('conteudo', $comunicado->conteudo ?? '') }}</textarea>
        @error('conteudo')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>
