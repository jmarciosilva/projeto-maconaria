@php
$documento ??= null;
$bloqueado = isset($documento) && in_array($documento->status->value, ['aprovado', 'publicado'], true);
@endphp

<div class="space-y-4">
    <div class="grid gap-4 md:grid-cols-3">
        <x-ui.select rotulo="Tipo" nome="tipo" :opcoes="$tipos->all()" :valor="$documento->tipo->value ?? 'ata'" :erro="$errors->first('tipo')" obrigatorio :disabled="$bloqueado" />
        <x-ui.select rotulo="Status" nome="status" :opcoes="$statusDisponiveis" :valor="$documento->status->value ?? 'rascunho'" :erro="$errors->first('status')" obrigatorio :disabled="$bloqueado" />
        <x-ui.input rotulo="Data do documento" nome="data_documento" tipo="date" :valor="old('data_documento', isset($documento?->data_documento) ? $documento->data_documento->format('Y-m-d') : now()->format('Y-m-d'))" :erro="$errors->first('data_documento')" :disabled="$bloqueado" />
    </div>

    <x-ui.input rotulo="Título" nome="titulo" :valor="$documento->titulo ?? null" :erro="$errors->first('titulo')" obrigatorio :disabled="$bloqueado" />

    <div>
        <label for="conteudo-editor" class="block text-sm font-medium text-gray-700">Conteúdo</label>
        <div id="conteudo-editor" data-quill-editor data-quill-target="conteudo-input" class="mt-1 bg-white @if ($bloqueado) pointer-events-none opacity-75 @endif">{!! old('conteudo', $documento->conteudo ?? '') !!}</div>
        <textarea name="conteudo" id="conteudo-input" class="hidden">{{ old('conteudo', $documento->conteudo ?? '') }}</textarea>
        @error('conteudo')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    @unless ($bloqueado)
        <div>
            <label for="arquivos" class="block text-sm font-medium text-gray-700">Arquivos anexos</label>
            <input id="arquivos" name="arquivos[]" type="file" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
            <p class="mt-1 text-xs text-gray-500">PDF, DOC, DOCX, XLS ou XLSX. Máximo de 10 MB por arquivo.</p>
            @error('arquivos.*')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    @endunless
</div>
