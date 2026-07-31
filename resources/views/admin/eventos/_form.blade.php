@php
$evento ??= null;
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
            <p class="mt-0.5 text-sm text-gray-500">Título exibido no evento e o endereço (URL) dele no site.</p>
        </div>
    </div>

    <div class="space-y-4">
        <x-ui.input rotulo="Título" nome="titulo" :valor="$evento->titulo ?? null" :erro="$errors->first('titulo')" obrigatorio />
        <x-ui.input rotulo="Slug" nome="slug" :valor="$evento->slug ?? null" :erro="$errors->first('slug')" placeholder="gerado automaticamente" obrigatorio />
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
            <p class="mt-0.5 text-sm text-gray-500">Tipo, status, visibilidade e local do evento.</p>
        </div>
    </div>

    <div class="space-y-4">
        <div class="grid gap-4 md:grid-cols-3">
            <x-ui.select rotulo="Tipo" nome="tipo" :opcoes="$tipos->all()" :valor="$evento->tipo->value ?? 'evento'" :erro="$errors->first('tipo')" obrigatorio />
            <x-ui.select rotulo="Status" nome="status" :opcoes="$statusDisponiveis->all()" :valor="$evento->status->value ?? 'rascunho'" :erro="$errors->first('status')" obrigatorio />
            <x-ui.select rotulo="Visibilidade" nome="visibilidade" :opcoes="$visibilidades->all()" :valor="$evento->visibilidade->value ?? 'publica'" :erro="$errors->first('visibilidade')" obrigatorio />
        </div>

        <x-ui.input rotulo="Local" nome="local" :valor="$evento->local ?? null" :erro="$errors->first('local')" />
    </div>
</section>

<section class="mt-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
    <div class="mb-5 flex items-start gap-3">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-green-100 text-green-700">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0V11.25A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
            </svg>
        </span>
        <div>
            <h2 class="text-base font-semibold text-gray-900">Data e horário</h2>
            <p class="mt-0.5 text-sm text-gray-500">Quando o evento começa e termina.</p>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <x-ui.input rotulo="Início" nome="inicio_em" tipo="datetime-local" :valor="old('inicio_em', isset($evento?->inicio_em) ? $evento->inicio_em->format('Y-m-d\TH:i') : null)" :erro="$errors->first('inicio_em')" obrigatorio />
        <x-ui.input rotulo="Término" nome="fim_em" tipo="datetime-local" :valor="old('fim_em', isset($evento?->fim_em) ? $evento->fim_em->format('Y-m-d\TH:i') : null)" :erro="$errors->first('fim_em')" />
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
            <h2 class="text-base font-semibold text-gray-900">Descrição e capa</h2>
            <p class="mt-0.5 text-sm text-gray-500">Texto e imagem exibidos nas listagens de eventos e na home.</p>
        </div>
    </div>

    <div class="space-y-5">
        <div>
            <label for="descricao" class="block text-sm font-medium text-gray-700">Descrição</label>
            <textarea id="descricao" name="descricao" rows="5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">{{ old('descricao', $evento->descricao ?? '') }}</textarea>
            @error('descricao')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div x-data="{ nomeArquivo: null }">
            <label class="block text-sm font-medium text-gray-700">Imagem de capa</label>

            <div class="mt-1.5 flex aspect-video w-full max-w-sm items-center justify-center overflow-hidden rounded-md border border-gray-200 bg-gray-50">
                @if (isset($evento) && $evento->imagem_capa)
                    <img src="{{ asset('storage/'.$evento->imagem_capa) }}" alt="Capa atual do evento" class="h-full w-full object-cover">
                @else
                    <svg class="h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3 12V4.5A1.5 1.5 0 0 1 4.5 3h15A1.5 1.5 0 0 1 21 4.5v15a1.5 1.5 0 0 1-1.5 1.5H4.5A1.5 1.5 0 0 1 3 19.5v-1.875M9 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" /></svg>
                @endif
            </div>

            <label for="imagem_capa" class="mt-2 inline-flex cursor-pointer items-center justify-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                Escolher imagem
            </label>
            <input type="file" id="imagem_capa" name="imagem_capa" accept="image/*" class="sr-only" @change="nomeArquivo = $event.target.files[0]?.name ?? null">
            <p class="mt-1.5 text-xs text-gray-500" x-text="nomeArquivo ?? 'JPG, PNG ou WebP, na horizontal (ideal: proporção 16:9). Máximo de 4 MB.'"></p>
            @error('imagem_capa')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
</section>

<section class="mt-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
    <div class="mb-5 flex items-start gap-3">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-indigo-700">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
        </span>
        <div>
            <h2 class="text-base font-semibold text-gray-900">Confirmação de presença</h2>
            <p class="mt-0.5 text-sm text-gray-500">Controle a inscrição de Irmãos para este evento pela Área Restrita.</p>
        </div>
    </div>

    <div class="space-y-4">
        <label class="flex items-center justify-between gap-3 rounded-md border border-gray-200 px-4 py-3">
            <span class="text-sm font-medium text-gray-700">Permitir confirmação de presença na área restrita</span>
            <input type="checkbox" name="permite_confirmacao" value="1" @checked(old('permite_confirmacao', $evento->permite_confirmacao ?? false)) class="rounded border-gray-300 text-blue-800 focus:ring-blue-700">
        </label>

        <div class="grid gap-4 md:grid-cols-2">
            <x-ui.input rotulo="Prazo de confirmação" nome="inscricoes_ate" tipo="datetime-local" :valor="old('inscricoes_ate', isset($evento?->inscricoes_ate) ? $evento->inscricoes_ate->format('Y-m-d\TH:i') : null)" :erro="$errors->first('inscricoes_ate')" />
            <x-ui.input rotulo="Capacidade" nome="capacidade" tipo="number" min="1" :valor="$evento->capacidade ?? null" :erro="$errors->first('capacidade')" />
        </div>
    </div>
</section>
