<section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
    <div class="mb-5 flex items-start gap-3">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-700">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5" />
            </svg>
        </span>
        <div>
            <h2 class="text-base font-semibold text-gray-900">Conteúdo do slide</h2>
            <p class="mt-0.5 text-sm text-gray-500">Textos exibidos sobre a imagem, na home do site.</p>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <x-ui.input rotulo="Título" nome="titulo" :valor="$item->titulo ?? null" :erro="$errors->first('titulo')" />
        <x-ui.input rotulo="Subtítulo" nome="subtitulo" :valor="$item->subtitulo ?? null" :erro="$errors->first('subtitulo')" />
        <x-ui.input rotulo="Link (opcional)" nome="link" tipo="url" :valor="$item->link ?? null" :erro="$errors->first('link')" placeholder="https://..." />
        <x-ui.input rotulo="Texto do botão" nome="texto_botao" :valor="$item->texto_botao ?? null" :erro="$errors->first('texto_botao')" />
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
            <h2 class="text-base font-semibold text-gray-900">Imagens</h2>
            <p class="mt-0.5 text-sm text-gray-500">Formato paisagem (16:9) recomendado. A versão mobile é opcional — se vazia, a versão desktop é usada.</p>
        </div>
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <div x-data="{ nomeArquivo: null }">
            <label class="block text-sm font-medium text-gray-700">
                Imagem para desktop @if (! isset($item)) <span class="text-red-600">*</span> @endif
            </label>

            <div class="mt-1.5 flex aspect-video w-full items-center justify-center overflow-hidden rounded-md border border-gray-200 bg-gray-50">
                @isset($item)
                    @if ($item->imagem_desktop)
                        <img src="{{ asset('storage/'.$item->imagem_desktop) }}" alt="{{ $item->texto_alternativo }}" class="h-full w-full object-cover">
                    @else
                        <svg class="h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3 12V4.5A1.5 1.5 0 0 1 4.5 3h15A1.5 1.5 0 0 1 21 4.5v15a1.5 1.5 0 0 1-1.5 1.5H4.5A1.5 1.5 0 0 1 3 19.5v-1.875M9 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" /></svg>
                    @endif
                @else
                    <svg class="h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3 12V4.5A1.5 1.5 0 0 1 4.5 3h15A1.5 1.5 0 0 1 21 4.5v15a1.5 1.5 0 0 1-1.5 1.5H4.5A1.5 1.5 0 0 1 3 19.5v-1.875M9 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" /></svg>
                @endif
            </div>

            <label for="imagem_desktop" class="mt-2 inline-flex cursor-pointer items-center justify-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                Escolher imagem
            </label>
            <input type="file" id="imagem_desktop" name="imagem_desktop" accept="image/*" class="sr-only" @change="nomeArquivo = $event.target.files[0]?.name ?? null">
            <p class="mt-1.5 text-xs text-gray-500" x-text="nomeArquivo ?? 'Nenhum arquivo selecionado.'"></p>
            @error('imagem_desktop')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div x-data="{ nomeArquivo: null }">
            <label class="block text-sm font-medium text-gray-700">Imagem para mobile (opcional)</label>

            <div class="mt-1.5 flex aspect-video w-full items-center justify-center overflow-hidden rounded-md border border-gray-200 bg-gray-50">
                @isset($item)
                    @if ($item->imagem_mobile)
                        <img src="{{ asset('storage/'.$item->imagem_mobile) }}" alt="{{ $item->texto_alternativo }}" class="h-full w-full object-cover">
                    @else
                        <svg class="h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3 12V4.5A1.5 1.5 0 0 1 4.5 3h15A1.5 1.5 0 0 1 21 4.5v15a1.5 1.5 0 0 1-1.5 1.5H4.5A1.5 1.5 0 0 1 3 19.5v-1.875M9 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" /></svg>
                    @endif
                @else
                    <svg class="h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3 12V4.5A1.5 1.5 0 0 1 4.5 3h15A1.5 1.5 0 0 1 21 4.5v15a1.5 1.5 0 0 1-1.5 1.5H4.5A1.5 1.5 0 0 1 3 19.5v-1.875M9 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" /></svg>
                @endif
            </div>

            <label for="imagem_mobile" class="mt-2 inline-flex cursor-pointer items-center justify-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                Escolher imagem
            </label>
            <input type="file" id="imagem_mobile" name="imagem_mobile" accept="image/*" class="sr-only" @change="nomeArquivo = $event.target.files[0]?.name ?? null">
            <p class="mt-1.5 text-xs text-gray-500" x-text="nomeArquivo ?? 'Nenhum arquivo selecionado.'"></p>
            @error('imagem_mobile')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="mt-5">
        <x-ui.input rotulo="Texto alternativo (acessibilidade)" nome="texto_alternativo" :valor="$item->texto_alternativo ?? null" :erro="$errors->first('texto_alternativo')" obrigatorio />
    </div>
</section>

<section class="mt-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
    <div class="mb-5 flex items-start gap-3">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0V11.25A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
            </svg>
        </span>
        <div>
            <h2 class="text-base font-semibold text-gray-900">Exibição</h2>
            <p class="mt-0.5 text-sm text-gray-500">Controle a ordem, o período e a visibilidade deste item no carrossel.</p>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <x-ui.input rotulo="Ordem de exibição" nome="ordem" tipo="number" :valor="$item->ordem ?? 0" :erro="$errors->first('ordem')" />
        <x-ui.input rotulo="Exibir a partir de" nome="data_inicio" tipo="date" :valor="optional($item->data_inicio ?? null)->format('Y-m-d')" :erro="$errors->first('data_inicio')" />
        <x-ui.input rotulo="Exibir até" nome="data_fim" tipo="date" :valor="optional($item->data_fim ?? null)->format('Y-m-d')" :erro="$errors->first('data_fim')" />
    </div>

    <div class="mt-5 space-y-2.5">
        <label class="flex items-center justify-between gap-3 rounded-md border border-gray-200 px-4 py-3">
            <span class="text-sm font-medium text-gray-700">Ativo</span>
            <input type="checkbox" name="ativo" value="1" @checked(old('ativo', $item->ativo ?? true)) class="rounded border-gray-300 text-blue-800 focus:ring-blue-700">
        </label>

        <label class="flex items-center justify-between gap-3 rounded-md border border-gray-200 px-4 py-3">
            <span class="text-sm font-medium text-gray-700">Abrir link em nova aba</span>
            <input type="checkbox" name="abrir_em_nova_aba" value="1" @checked(old('abrir_em_nova_aba', $item->abrir_em_nova_aba ?? false)) class="rounded border-gray-300 text-blue-800 focus:ring-blue-700">
        </label>
    </div>
</section>
