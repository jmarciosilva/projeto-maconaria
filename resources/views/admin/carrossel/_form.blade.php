<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <x-ui.input rotulo="Título" nome="titulo" :valor="$item->titulo ?? null" :erro="$errors->first('titulo')" />
    <x-ui.input rotulo="Subtítulo" nome="subtitulo" :valor="$item->subtitulo ?? null" :erro="$errors->first('subtitulo')" />
    <x-ui.input rotulo="Link (opcional)" nome="link" tipo="url" :valor="$item->link ?? null" :erro="$errors->first('link')" placeholder="https://..." />
    <x-ui.input rotulo="Texto do botão" nome="texto_botao" :valor="$item->texto_botao ?? null" :erro="$errors->first('texto_botao')" />
    <x-ui.input rotulo="Ordem de exibição" nome="ordem" tipo="number" :valor="$item->ordem ?? 0" :erro="$errors->first('ordem')" />
    <x-ui.input rotulo="Texto alternativo (acessibilidade)" nome="texto_alternativo" :valor="$item->texto_alternativo ?? null" :erro="$errors->first('texto_alternativo')" obrigatorio />
    <x-ui.input rotulo="Exibir a partir de" nome="data_inicio" tipo="date" :valor="optional($item->data_inicio ?? null)->format('Y-m-d')" :erro="$errors->first('data_inicio')" />
    <x-ui.input rotulo="Exibir até" nome="data_fim" tipo="date" :valor="optional($item->data_fim ?? null)->format('Y-m-d')" :erro="$errors->first('data_fim')" />
</div>

<div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
    <div>
        <label for="imagem_desktop" class="block text-sm font-medium text-gray-700">
            Imagem para desktop @if (! isset($item)) <span class="text-red-600">*</span> @endif
        </label>

        @isset($item)
            @if ($item->imagem_desktop)
                <img src="{{ asset('storage/'.$item->imagem_desktop) }}" alt="{{ $item->texto_alternativo }}" class="mt-2 h-24 w-full rounded object-cover">
            @endif
        @endisset

        <input type="file" id="imagem_desktop" name="imagem_desktop" accept="image/*" class="mt-1 block w-full text-sm text-gray-700">
        @error('imagem_desktop')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="imagem_mobile" class="block text-sm font-medium text-gray-700">Imagem para mobile (opcional)</label>

        @isset($item)
            @if ($item->imagem_mobile)
                <img src="{{ asset('storage/'.$item->imagem_mobile) }}" alt="{{ $item->texto_alternativo }}" class="mt-2 h-24 w-full rounded object-cover">
            @endif
        @endisset

        <input type="file" id="imagem_mobile" name="imagem_mobile" accept="image/*" class="mt-1 block w-full text-sm text-gray-700">
        @error('imagem_mobile')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="mt-4 space-y-2">
    <label class="flex items-center gap-2 text-sm text-gray-700">
        <input type="checkbox" name="ativo" value="1" @checked(old('ativo', $item->ativo ?? true)) class="rounded border-gray-300 text-blue-800 focus:ring-blue-700">
        Ativo
    </label>

    <label class="flex items-center gap-2 text-sm text-gray-700">
        <input type="checkbox" name="abrir_em_nova_aba" value="1" @checked(old('abrir_em_nova_aba', $item->abrir_em_nova_aba ?? false)) class="rounded border-gray-300 text-blue-800 focus:ring-blue-700">
        Abrir link em nova aba
    </label>
</div>
