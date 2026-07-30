@php
$evento ??= null;
@endphp

<div class="space-y-4">
    <x-ui.input rotulo="Título" nome="titulo" :valor="$evento->titulo ?? null" :erro="$errors->first('titulo')" obrigatorio />

    <x-ui.input rotulo="Slug" nome="slug" :valor="$evento->slug ?? null" :erro="$errors->first('slug')" placeholder="gerado automaticamente" obrigatorio />

    <div class="grid gap-4 md:grid-cols-3">
        <x-ui.select rotulo="Tipo" nome="tipo" :opcoes="$tipos->all()" :valor="$evento->tipo->value ?? 'evento'" :erro="$errors->first('tipo')" obrigatorio />
        <x-ui.select rotulo="Status" nome="status" :opcoes="$statusDisponiveis->all()" :valor="$evento->status->value ?? 'rascunho'" :erro="$errors->first('status')" obrigatorio />
        <x-ui.select rotulo="Visibilidade" nome="visibilidade" :opcoes="$visibilidades->all()" :valor="$evento->visibilidade->value ?? 'publica'" :erro="$errors->first('visibilidade')" obrigatorio />
    </div>

    <x-ui.input rotulo="Local" nome="local" :valor="$evento->local ?? null" :erro="$errors->first('local')" />

    <div class="grid gap-4 md:grid-cols-2">
        <x-ui.input rotulo="Início" nome="inicio_em" tipo="datetime-local" :valor="old('inicio_em', isset($evento?->inicio_em) ? $evento->inicio_em->format('Y-m-d\TH:i') : null)" :erro="$errors->first('inicio_em')" obrigatorio />
        <x-ui.input rotulo="Término" nome="fim_em" tipo="datetime-local" :valor="old('fim_em', isset($evento?->fim_em) ? $evento->fim_em->format('Y-m-d\TH:i') : null)" :erro="$errors->first('fim_em')" />
    </div>

    <div>
        <label for="descricao" class="block text-sm font-medium text-gray-700">Descrição</label>
        <textarea id="descricao" name="descricao" rows="5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">{{ old('descricao', $evento->descricao ?? '') }}</textarea>
        @error('descricao')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <fieldset class="space-y-4 rounded-md border border-gray-200 p-4">
        <legend class="px-1 text-sm font-semibold text-gray-900">Confirmação de presença</legend>

        <label class="flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" name="permite_confirmacao" value="1" @checked(old('permite_confirmacao', $evento->permite_confirmacao ?? false)) class="rounded border-gray-300 text-blue-800 focus:ring-blue-700">
            Permitir confirmação de presença na área restrita
        </label>

        <div class="grid gap-4 md:grid-cols-2">
            <x-ui.input rotulo="Prazo de confirmação" nome="inscricoes_ate" tipo="datetime-local" :valor="old('inscricoes_ate', isset($evento?->inscricoes_ate) ? $evento->inscricoes_ate->format('Y-m-d\TH:i') : null)" :erro="$errors->first('inscricoes_ate')" />
            <x-ui.input rotulo="Capacidade" nome="capacidade" tipo="number" min="1" :valor="$evento->capacidade ?? null" :erro="$errors->first('capacidade')" />
        </div>
    </fieldset>
</div>
