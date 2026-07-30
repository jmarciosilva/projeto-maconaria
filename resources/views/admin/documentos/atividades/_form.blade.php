@php($atividade ??= null)

<div class="space-y-4">
    <x-ui.input rotulo="Título" nome="titulo" :valor="$atividade->titulo ?? null" :erro="$errors->first('titulo')" obrigatorio />

    <div class="grid gap-4 md:grid-cols-2">
        <x-ui.select rotulo="Status" nome="status" :opcoes="$statusDisponiveis->all()" :valor="$atividade->status->value ?? 'rascunho'" :erro="$errors->first('status')" obrigatorio />
        <x-ui.input rotulo="Prazo de entrega" nome="prazo_entrega_em" tipo="datetime-local" :valor="old('prazo_entrega_em', isset($atividade?->prazo_entrega_em) ? $atividade->prazo_entrega_em->format('Y-m-d\TH:i') : null)" :erro="$errors->first('prazo_entrega_em')" />
    </div>

    <div>
        <label for="descricao" class="block text-sm font-medium text-gray-700">Descrição</label>
        <textarea id="descricao" name="descricao" rows="6" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">{{ old('descricao', $atividade->descricao ?? '') }}</textarea>
        @error('descricao')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="arquivos" class="block text-sm font-medium text-gray-700">Arquivos privados da atividade</label>
        <input id="arquivos" name="arquivos[]" type="file" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
        <p class="mt-1 text-xs text-gray-500">PDF, DOC, DOCX, XLS, XLSX, PPT ou PPTX. Máximo de 10 MB por arquivo.</p>
        @error('arquivos.*')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>
