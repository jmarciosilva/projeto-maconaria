@php($album ??= null)

<section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
    <div class="mb-5 flex items-start gap-3">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-700">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
            </svg>
        </span>
        <div>
            <h2 class="text-base font-semibold text-gray-900">Identificação</h2>
            <p class="mt-0.5 text-sm text-gray-500">Título exibido no álbum e o endereço (URL) dele no site.</p>
        </div>
    </div>

    <div class="space-y-4">
        <x-ui.input rotulo="Título" nome="titulo" :valor="$album->titulo ?? null" :erro="$errors->first('titulo')" obrigatorio />
        <x-ui.input rotulo="Slug" nome="slug" :valor="$album->slug ?? null" :erro="$errors->first('slug')" />

        <div class="grid gap-4 md:grid-cols-2">
            <x-ui.select rotulo="Status" nome="status" :opcoes="$statusDisponiveis->all()" :valor="$album->status->value ?? 'rascunho'" :erro="$errors->first('status')" obrigatorio />
            <x-ui.select rotulo="Visibilidade" nome="visibilidade" :opcoes="$visibilidades->all()" :valor="$album->visibilidade->value ?? 'restrita'" :erro="$errors->first('visibilidade')" obrigatorio />
        </div>
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
            <h2 class="text-base font-semibold text-gray-900">Descrição e fotografias</h2>
            <p class="mt-0.5 text-sm text-gray-500">Texto e imagens exibidos neste álbum da galeria.</p>
        </div>
    </div>

    <div class="space-y-5">
        <div>
            <label for="descricao" class="block text-sm font-medium text-gray-700">Descrição</label>
            <textarea id="descricao" name="descricao" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">{{ old('descricao', $album->descricao ?? '') }}</textarea>
        </div>

        <div x-data="{ arquivos: [] }">
            <label class="block text-sm font-medium text-gray-700">Fotografias</label>

            <label for="fotografias" class="mt-1.5 inline-flex cursor-pointer items-center justify-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                Escolher fotografias
            </label>
            <input
                id="fotografias" name="fotografias[]" type="file" multiple accept="image/*"
                class="sr-only"
                @change="arquivos = Array.from($event.target.files).map(f => f.name)"
            >
            <p class="mt-1.5 text-xs text-gray-500" x-show="arquivos.length === 0">Imagens JPG, PNG ou WebP. Máximo de 5 MB por foto.</p>
            <ul class="mt-1.5 space-y-0.5 text-xs text-gray-600" x-show="arquivos.length > 0">
                <template x-for="nome in arquivos" :key="nome">
                    <li x-text="nome"></li>
                </template>
            </ul>
            @error('fotografias.*')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
</section>
