<div class="space-y-4">
    <x-ui.input rotulo="Título" nome="titulo" :valor="$pagina->titulo ?? null" :erro="$errors->first('titulo')" obrigatorio />

    <x-ui.input
        rotulo="Slug (identificador da URL)"
        nome="slug"
        :valor="$pagina->slug ?? null"
        :erro="$errors->first('slug')"
        obrigatorio
        placeholder="ex.: sobre-nos"
        @if (isset($pagina) && in_array($pagina->slug, ['sobre-nos', 'maconaria', 'maconaria-jovens', 'mudar-cidadao', 'politica-privacidade', 'termos-de-uso'], true)) readonly @endif
    />

    @if (isset($pagina) && in_array($pagina->slug, ['sobre-nos', 'maconaria', 'maconaria-jovens', 'mudar-cidadao', 'politica-privacidade', 'termos-de-uso'], true))
        <p class="text-xs text-gray-500">Esta página possui uma URL fixa do site (definida no escopo do projeto) e o slug não pode ser alterado.</p>
    @endif

    <div>
        <label for="conteudo-editor" class="block text-sm font-medium text-gray-700">Conteúdo</label>
        <div id="conteudo-editor" data-quill-editor data-quill-target="conteudo-input" class="mt-1 bg-white">{!! old('conteudo', $pagina->conteudo ?? '') !!}</div>
        <textarea name="conteudo" id="conteudo-input" class="hidden">{{ old('conteudo', $pagina->conteudo ?? '') }}</textarea>
        @error('conteudo')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <fieldset class="space-y-4 rounded-md border border-gray-200 p-4">
        <legend class="px-1 text-sm font-semibold text-gray-900">SEO</legend>

        <x-ui.input rotulo="Meta título" nome="meta_titulo" :valor="$pagina->meta_titulo ?? null" :erro="$errors->first('meta_titulo')" placeholder="Se vazio, usa o título da página" />
        <x-ui.input rotulo="Meta descrição" nome="meta_descricao" :valor="$pagina->meta_descricao ?? null" :erro="$errors->first('meta_descricao')" />
    </fieldset>

    <label class="flex items-center gap-2 text-sm text-gray-700">
        <input type="checkbox" name="publicado" value="1" @checked(old('publicado', $pagina->publicado ?? true)) class="rounded border-gray-300 text-blue-800 focus:ring-blue-700">
        Publicado (visível no site)
    </label>
</div>
