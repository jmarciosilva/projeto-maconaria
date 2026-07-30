@php($publicacao ??= null)
<div class="space-y-4">
    <x-ui.input rotulo="Título" nome="titulo" :valor="$publicacao->titulo ?? null" :erro="$errors->first('titulo')" obrigatorio />
    <div class="grid gap-4 md:grid-cols-2">
        <x-ui.select rotulo="Status" nome="status" :opcoes="$statusDisponiveis->all()" :valor="$publicacao->status->value ?? 'rascunho'" :erro="$errors->first('status')" obrigatorio />
        <x-ui.select rotulo="Visibilidade" nome="visibilidade" :opcoes="$visibilidades->all()" :valor="$publicacao->visibilidade->value ?? 'restrita'" :erro="$errors->first('visibilidade')" obrigatorio />
    </div>
    <div>
        <label for="conteudo" class="block text-sm font-medium text-gray-700">Conteúdo</label>
        <textarea id="conteudo" name="conteudo" rows="6" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>{{ old('conteudo', $publicacao->conteudo ?? '') }}</textarea>
    </div>

    <div>
        <label for="imagem_capa" class="block text-sm font-medium text-gray-700">Imagem de capa (exibida em destaque na página inicial)</label>

        @isset($publicacao)
            @if ($publicacao->imagem_capa)
                <img src="{{ asset('storage/'.$publicacao->imagem_capa) }}" alt="Capa atual da publicação" class="mt-2 aspect-video w-full max-w-sm rounded-md object-cover">
            @endif
        @endisset

        <input type="file" id="imagem_capa" name="imagem_capa" accept="image/*" class="mt-1 block w-full text-sm text-gray-700">
        <p class="mt-1 text-xs text-gray-500">Imagem JPG, PNG ou WebP, na horizontal (ideal: proporção 16:9). Máximo de 4 MB. A foto é recortada automaticamente para preencher o espaço reservado na página inicial.</p>
        @error('imagem_capa')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>
