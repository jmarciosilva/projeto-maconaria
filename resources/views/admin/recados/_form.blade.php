@php($recado ??= null)
<div class="space-y-4">
    <x-ui.select rotulo="Categoria" nome="categoria" :opcoes="$categoriasDisponiveis->all()" :valor="$recado->categoria->value ?? 'geral'" :erro="$errors->first('categoria')" obrigatorio />
    <x-ui.input rotulo="Título" nome="titulo" :valor="$recado->titulo ?? null" :erro="$errors->first('titulo')" obrigatorio />

    <div>
        <label for="conteudo-editor" class="block text-sm font-medium text-gray-700">Conteúdo</label>
        <div id="conteudo-editor" data-quill-editor data-quill-target="conteudo-input" class="mt-1 bg-white">{!! old('conteudo', $recado->conteudo ?? '') !!}</div>
        <textarea name="conteudo" id="conteudo-input" class="hidden">{{ old('conteudo', $recado->conteudo ?? '') }}</textarea>
        @error('conteudo')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <x-ui.input rotulo="Publicado em" nome="publicado_em" tipo="datetime-local" :valor="optional($recado->publicado_em ?? null)->format('Y-m-d\TH:i')" :erro="$errors->first('publicado_em')" />
        <x-ui.input rotulo="Válido até" nome="valido_ate" tipo="date" :valor="optional($recado->valido_ate ?? null)->format('Y-m-d')" :erro="$errors->first('valido_ate')" />
    </div>
    <p class="text-xs text-gray-500">Deixe "Publicado em" em branco para publicar imediatamente ao ativar. "Válido até" é opcional e faz o recado sumir do painel automaticamente após a data (ex.: prazo da mensalidade).</p>

    <label class="flex items-center gap-2 text-sm font-medium text-gray-700">
        <input type="hidden" name="ativo" value="0">
        <input type="checkbox" name="ativo" value="1" class="rounded border-gray-300 text-blue-900 focus:ring-blue-900" @checked(old('ativo', $recado->ativo ?? true))>
        Ativo (visível no painel dos usuários)
    </label>
</div>
