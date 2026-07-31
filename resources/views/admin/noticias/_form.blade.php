@php
$noticia ??= null;
$tagsSelecionadas = collect(old('tags', $noticia?->tags->pluck('id')->all() ?? []))->map(fn ($id) => (int) $id)->all();
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
            <p class="mt-0.5 text-sm text-gray-500">Título exibido na notícia e o endereço (URL) dela no site.</p>
        </div>
    </div>

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
            <p class="mt-0.5 text-sm text-gray-500">Categoria, status e quando esta notícia fica visível.</p>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <x-ui.select rotulo="Categoria" nome="categoria_id" :opcoes="['' => 'Sem categoria'] + $categorias->all()" :valor="$noticia->categoria_id ?? null" :erro="$errors->first('categoria_id')" />
        <x-ui.select rotulo="Status" nome="status" :opcoes="$statusDisponiveis->all()" :valor="$noticia->status->value ?? 'rascunho'" :erro="$errors->first('status')" obrigatorio />
        <x-ui.select
            rotulo="Visibilidade"
            nome="visibilidade"
            :opcoes="['publica' => 'Pública', 'restrita' => 'Restrita']"
            :valor="$noticia->visibilidade->value ?? 'publica'"
            :erro="$errors->first('visibilidade')"
            obrigatorio
        />
        <x-ui.input rotulo="Publicado em" nome="publicado_em" tipo="datetime-local" :valor="old('publicado_em', isset($noticia?->publicado_em) ? $noticia->publicado_em->format('Y-m-d\TH:i') : null)" :erro="$errors->first('publicado_em')" />
        <x-ui.input rotulo="Agendado para" nome="agendado_para" tipo="datetime-local" :valor="old('agendado_para', isset($noticia?->agendado_para) ? $noticia->agendado_para->format('Y-m-d\TH:i') : null)" :erro="$errors->first('agendado_para')" />
    </div>
</section>

<section class="mt-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
    <div class="mb-5 flex items-start gap-3">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-purple-100 text-purple-700">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3 12V4.5A1.5 1.5 0 0 1 4.5 3h15A1.5 1.5 0 0 1 21 4.5v15a1.5 1.5 0 0 1-1.5 1.5H4.5A1.5 1.5 0 0 1 3 19.5v-1.875M9 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
            </svg>
        </span>
        <div>
            <h2 class="text-base font-semibold text-gray-900">Capa e resumo</h2>
            <p class="mt-0.5 text-sm text-gray-500">Imagem e texto curto exibidos nas listagens de notícias e na home.</p>
        </div>
    </div>

    <div class="space-y-5">
        <div x-data="{ nomeArquivo: null }">
            <label class="block text-sm font-medium text-gray-700">Imagem de capa</label>

            <div class="mt-1.5 flex aspect-[16/10] w-full max-w-sm items-center justify-center overflow-hidden rounded-md border border-gray-200 bg-gray-50">
                @if (isset($noticia) && $noticia->imagem_capa)
                    <img src="{{ asset('storage/'.$noticia->imagem_capa) }}" alt="Capa atual da notícia" class="h-full w-full object-cover">
                @else
                    <svg class="h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3 12V4.5A1.5 1.5 0 0 1 4.5 3h15A1.5 1.5 0 0 1 21 4.5v15a1.5 1.5 0 0 1-1.5 1.5H4.5A1.5 1.5 0 0 1 3 19.5v-1.875M9 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" /></svg>
                @endif
            </div>

            <label for="imagem_capa" class="mt-2 inline-flex cursor-pointer items-center justify-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                Escolher imagem
            </label>
            <input type="file" id="imagem_capa" name="imagem_capa" accept="image/*" class="sr-only" @change="nomeArquivo = $event.target.files[0]?.name ?? null">
            <p class="mt-1.5 text-xs text-gray-500" x-text="nomeArquivo ?? 'JPG, PNG ou WebP, na horizontal (ideal: proporção 16:9 ou 16:10). Máximo de 4 MB.'"></p>
            @error('imagem_capa')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="resumo" class="block text-sm font-medium text-gray-700">Resumo</label>
            <textarea id="resumo" name="resumo" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">{{ old('resumo', $noticia->resumo ?? '') }}</textarea>
            @error('resumo')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
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
            <p class="mt-0.5 text-sm text-gray-500">Texto completo da notícia, com formatação rica.</p>
        </div>
    </div>

    <div id="conteudo-editor" data-quill-editor data-quill-target="conteudo-input" class="bg-white">{!! old('conteudo', $noticia->conteudo ?? '') !!}</div>
    <textarea name="conteudo" id="conteudo-input" class="hidden">{{ old('conteudo', $noticia->conteudo ?? '') }}</textarea>
    @error('conteudo')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</section>

<section class="mt-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
    <div class="mb-5 flex items-start gap-3">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-indigo-700">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.169.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
            </svg>
        </span>
        <div>
            <h2 class="text-base font-semibold text-gray-900">Tags e destaque</h2>
            <p class="mt-0.5 text-sm text-gray-500">Organização e destaque desta notícia na página inicial.</p>
        </div>
    </div>

    <div class="space-y-5">
        @if ($tags->isNotEmpty())
            <div>
                <span class="block text-sm font-medium text-gray-700">Tags</span>
                <div class="mt-2 grid gap-2.5 sm:grid-cols-2">
                    @foreach ($tags as $tag)
                        <label class="flex items-center justify-between gap-3 rounded-md border border-gray-200 px-4 py-3">
                            <span class="text-sm font-medium text-gray-700">{{ $tag->nome }}</span>
                            <input type="checkbox" name="tags[]" value="{{ $tag->id }}" @checked(in_array($tag->id, $tagsSelecionadas, true)) class="rounded border-gray-300 text-blue-800 focus:ring-blue-700">
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        <label class="flex items-center justify-between gap-3 rounded-md border border-gray-200 px-4 py-3">
            <span class="text-sm font-medium text-gray-700">Destacar na página inicial</span>
            <input type="checkbox" name="destaque" value="1" @checked(old('destaque', $noticia->destaque ?? false)) class="rounded border-gray-300 text-blue-800 focus:ring-blue-700">
        </label>
    </div>
</section>
