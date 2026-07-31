@php
$slugFixo = isset($pagina) && in_array($pagina->slug, ['sobre-nos', 'maconaria', 'maconaria-jovens', 'mudar-cidadao', 'politica-privacidade', 'termos-de-uso'], true);
@endphp

<section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
    <div class="mb-5 flex items-start gap-3">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-700">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
            </svg>
        </span>
        <div>
            <h2 class="text-base font-semibold text-gray-900">Identificação</h2>
            <p class="mt-0.5 text-sm text-gray-500">Título exibido na página e o endereço (URL) dela no site.</p>
        </div>
    </div>

    <div class="space-y-4">
        <x-ui.input rotulo="Título" nome="titulo" :valor="$pagina->titulo ?? null" :erro="$errors->first('titulo')" obrigatorio />

        <div>
            <x-ui.input
                rotulo="Slug (identificador da URL)"
                nome="slug"
                :valor="$pagina->slug ?? null"
                :erro="$errors->first('slug')"
                obrigatorio
                placeholder="ex.: sobre-nos"
                :readonly="$slugFixo"
            />

            @if ($slugFixo)
                <p class="mt-1 text-xs text-gray-500">Esta página possui uma URL fixa do site (definida no escopo do projeto) e o slug não pode ser alterado.</p>
            @endif
        </div>
    </div>
</section>

<section class="mt-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
    <div class="mb-5 flex items-start gap-3">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-green-100 text-green-700">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487 18.549 2.8a2.036 2.036 0 1 1 2.879 2.879l-1.687 1.687m-2.879-2.879-9.193 9.193a4.5 4.5 0 0 0-1.128 1.897l-.674 2.245 2.245-.674a4.5 4.5 0 0 0 1.897-1.128l9.193-9.193m-2.879-2.879 2.879 2.879M6 18h12" />
            </svg>
        </span>
        <div>
            <h2 class="text-base font-semibold text-gray-900">Conteúdo</h2>
            <p class="mt-0.5 text-sm text-gray-500">Texto exibido no corpo da página, com formatação rica.</p>
        </div>
    </div>

    <div id="conteudo-editor" data-quill-editor data-quill-target="conteudo-input" class="bg-white">{!! old('conteudo', $pagina->conteudo ?? '') !!}</div>
    <textarea name="conteudo" id="conteudo-input" class="hidden">{{ old('conteudo', $pagina->conteudo ?? '') }}</textarea>
    @error('conteudo')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</section>

<section class="mt-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
    <div class="mb-5 flex items-start gap-3">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-purple-100 text-purple-700">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
        </span>
        <div>
            <h2 class="text-base font-semibold text-gray-900">SEO</h2>
            <p class="mt-0.5 text-sm text-gray-500">Como esta página aparece em buscadores e ao ser compartilhada.</p>
        </div>
    </div>

    <div class="space-y-4">
        <x-ui.input rotulo="Meta título" nome="meta_titulo" :valor="$pagina->meta_titulo ?? null" :erro="$errors->first('meta_titulo')" placeholder="Se vazio, usa o título da página" />
        <x-ui.input rotulo="Meta descrição" nome="meta_descricao" :valor="$pagina->meta_descricao ?? null" :erro="$errors->first('meta_descricao')" />
    </div>
</section>

<section class="mt-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
    <div class="mb-5 flex items-start gap-3">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            </svg>
        </span>
        <div>
            <h2 class="text-base font-semibold text-gray-900">Publicação</h2>
            <p class="mt-0.5 text-sm text-gray-500">Controle se esta página fica visível para os visitantes do site.</p>
        </div>
    </div>

    <label class="flex items-center justify-between gap-3 rounded-md border border-gray-200 px-4 py-3">
        <span class="text-sm font-medium text-gray-700">Publicado (visível no site)</span>
        <input type="checkbox" name="publicado" value="1" @checked(old('publicado', $pagina->publicado ?? true)) class="rounded border-gray-300 text-blue-800 focus:ring-blue-700">
    </label>
</section>
