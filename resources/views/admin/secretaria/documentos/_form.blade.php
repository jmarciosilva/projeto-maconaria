@php
$documento ??= null;
$bloqueado = isset($documento) && in_array($documento->status->value, ['aprovado', 'publicado'], true);
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
            <p class="mt-0.5 text-sm text-gray-500">Tipo, status e título do documento.</p>
            @if ($bloqueado)
                <p class="mt-1.5 text-xs font-medium text-amber-700">Documento aprovado ou publicado: os campos abaixo não podem mais ser alterados.</p>
            @endif
        </div>
    </div>

    <div class="space-y-4">
        <div class="grid gap-4 md:grid-cols-3">
            <x-ui.select rotulo="Tipo" nome="tipo" :opcoes="$tipos->all()" :valor="$documento->tipo->value ?? 'ata'" :erro="$errors->first('tipo')" obrigatorio :disabled="$bloqueado" />
            <x-ui.select rotulo="Status" nome="status" :opcoes="$statusDisponiveis" :valor="$documento->status->value ?? 'rascunho'" :erro="$errors->first('status')" obrigatorio :disabled="$bloqueado" />
            <x-ui.input rotulo="Data do documento" nome="data_documento" tipo="date" :valor="old('data_documento', isset($documento?->data_documento) ? $documento->data_documento->format('Y-m-d') : now()->format('Y-m-d'))" :erro="$errors->first('data_documento')" :disabled="$bloqueado" />
        </div>

        <x-ui.input rotulo="Título" nome="titulo" :valor="$documento->titulo ?? null" :erro="$errors->first('titulo')" obrigatorio :disabled="$bloqueado" />
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
            <p class="mt-0.5 text-sm text-gray-500">Texto completo do documento, com formatação rica.</p>
        </div>
    </div>

    <div id="conteudo-editor" data-quill-editor data-quill-target="conteudo-input" class="bg-white @if ($bloqueado) pointer-events-none opacity-75 @endif">{!! old('conteudo', $documento->conteudo ?? '') !!}</div>
    <textarea name="conteudo" id="conteudo-input" class="hidden">{{ old('conteudo', $documento->conteudo ?? '') }}</textarea>
    @error('conteudo')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</section>

@unless ($bloqueado)
    <section class="mt-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <div class="mb-5 flex items-start gap-3">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-purple-100 text-purple-700">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
            </span>
            <div>
                <h2 class="text-base font-semibold text-gray-900">Arquivos anexos</h2>
                <p class="mt-0.5 text-sm text-gray-500">PDF, DOC, DOCX, XLS ou XLSX. Máximo de 10 MB por arquivo.</p>
            </div>
        </div>

        <div x-data="{ arquivos: [] }">
            <label for="arquivos" class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                Escolher arquivos
            </label>
            <input
                id="arquivos" name="arquivos[]" type="file" multiple
                accept=".pdf,.doc,.docx,.xls,.xlsx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                class="sr-only"
                @change="arquivos = Array.from($event.target.files).map(f => f.name)"
            >
            <p class="mt-1.5 text-xs text-gray-500" x-show="arquivos.length === 0">Nenhum arquivo selecionado.</p>
            <ul class="mt-1.5 space-y-0.5 text-xs text-gray-600" x-show="arquivos.length > 0">
                <template x-for="nome in arquivos" :key="nome">
                    <li x-text="nome"></li>
                </template>
            </ul>
            @error('arquivos.*')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </section>
@endunless
