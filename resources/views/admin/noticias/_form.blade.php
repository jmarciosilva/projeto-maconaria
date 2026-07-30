@php
$noticia ??= null;
$tagsSelecionadas = collect(old('tags', $noticia?->tags->pluck('id')->all() ?? []))->map(fn ($id) => (int) $id)->all();
@endphp

<div class="space-y-4">
    <x-ui.input rotulo="Título" nome="titulo" :valor="$noticia->titulo ?? null" :erro="$errors->first('titulo')" obrigatorio />

    <x-ui.input
        rotulo="Slug"
        nome="slug"
        :valor="$noticia->slug ?? null"
        :erro="$errors->first('slug')"
        obrigatorio
        placeholder="ex.: comunicado-da-semana"
    />

    <div class="grid gap-4 md:grid-cols-2">
        <x-ui.select rotulo="Categoria" nome="categoria_id" :opcoes="['' => 'Sem categoria'] + $categorias->all()" :valor="$noticia->categoria_id ?? null" :erro="$errors->first('categoria_id')" />
        <x-ui.select rotulo="Status" nome="status" :opcoes="$statusDisponiveis->all()" :valor="$noticia->status->value ?? 'rascunho'" :erro="$errors->first('status')" obrigatorio />
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <x-ui.select
            rotulo="Visibilidade"
            nome="visibilidade"
            :opcoes="['publica' => 'Pública', 'restrita' => 'Restrita']"
            :valor="$noticia->visibilidade->value ?? 'publica'"
            :erro="$errors->first('visibilidade')"
            obrigatorio
        />
        <x-ui.input rotulo="Publicado em" nome="publicado_em" tipo="datetime-local" :valor="old('publicado_em', isset($noticia?->publicado_em) ? $noticia->publicado_em->format('Y-m-d\TH:i') : null)" :erro="$errors->first('publicado_em')" />
    </div>

    <x-ui.input rotulo="Agendado para" nome="agendado_para" tipo="datetime-local" :valor="old('agendado_para', isset($noticia?->agendado_para) ? $noticia->agendado_para->format('Y-m-d\TH:i') : null)" :erro="$errors->first('agendado_para')" />

    <div>
        <label for="resumo" class="block text-sm font-medium text-gray-700">Resumo</label>
        <textarea id="resumo" name="resumo" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">{{ old('resumo', $noticia->resumo ?? '') }}</textarea>
        @error('resumo')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="conteudo-editor" class="block text-sm font-medium text-gray-700">Conteúdo</label>
        <div id="conteudo-editor" data-quill-editor data-quill-target="conteudo-input" class="mt-1 bg-white">{!! old('conteudo', $noticia->conteudo ?? '') !!}</div>
        <textarea name="conteudo" id="conteudo-input" class="hidden">{{ old('conteudo', $noticia->conteudo ?? '') }}</textarea>
        @error('conteudo')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    @if ($tags->isNotEmpty())
        <fieldset class="space-y-2 rounded-md border border-gray-200 p-4">
            <legend class="px-1 text-sm font-semibold text-gray-900">Tags</legend>

            <div class="grid gap-2 sm:grid-cols-2">
                @foreach ($tags as $tag)
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="tags[]" value="{{ $tag->id }}" @checked(in_array($tag->id, $tagsSelecionadas, true)) class="rounded border-gray-300 text-blue-800 focus:ring-blue-700">
                        {{ $tag->nome }}
                    </label>
                @endforeach
            </div>
        </fieldset>
    @endif

    <label class="flex items-center gap-2 text-sm text-gray-700">
        <input type="checkbox" name="destaque" value="1" @checked(old('destaque', $noticia->destaque ?? false)) class="rounded border-gray-300 text-blue-800 focus:ring-blue-700">
        Destacar na página inicial
    </label>
</div>
