<x-layouts.admin titulo="Configurações Institucionais">
    <form method="POST" action="{{ route('admin.configuracoes.institucional.update') }}" enctype="multipart/form-data" class="max-w-2xl space-y-8 rounded-lg bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')

        <fieldset class="space-y-4">
            <legend class="text-sm font-semibold text-gray-900">Logotipo</legend>

            @if ($configuracao->logotipo)
                <img src="{{ asset('storage/'.$configuracao->logotipo) }}" alt="Logotipo atual da Loja" class="h-20 w-20 rounded-full object-contain ring-2 ring-gray-200">
            @endif

            <div>
                <label for="logotipo" class="block text-sm font-medium text-gray-700">Substituir logotipo</label>
                <input type="file" id="logotipo" name="logotipo" accept="image/*" class="mt-1 block w-full text-sm text-gray-700">
                @error('logotipo')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </fieldset>

        <fieldset class="space-y-4">
            <legend class="text-sm font-semibold text-gray-900">Contato e rodapé</legend>

            <div>
                <label for="endereco_rodape" class="block text-sm font-medium text-gray-700">Endereço (exibido no rodapé)</label>
                <textarea id="endereco_rodape" name="endereco_rodape" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">{{ old('endereco_rodape', $configuracao->endereco_rodape) }}</textarea>
                @error('endereco_rodape')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <x-ui.input rotulo="E-mail institucional" nome="email_institucional" tipo="email" :valor="$configuracao->email_institucional" :erro="$errors->first('email_institucional')" />
        </fieldset>

        <fieldset class="space-y-4">
            <legend class="text-sm font-semibold text-gray-900">Redes sociais</legend>

            <x-ui.input rotulo="Facebook" nome="facebook_url" tipo="url" :valor="$configuracao->facebook_url" :erro="$errors->first('facebook_url')" placeholder="https://facebook.com/..." />
            <x-ui.input rotulo="Instagram" nome="instagram_url" tipo="url" :valor="$configuracao->instagram_url" :erro="$errors->first('instagram_url')" placeholder="https://instagram.com/..." />
            <x-ui.input rotulo="Twitter / X" nome="twitter_url" tipo="url" :valor="$configuracao->twitter_url" :erro="$errors->first('twitter_url')" placeholder="https://x.com/..." />
            <x-ui.input rotulo="TikTok" nome="tiktok_url" tipo="url" :valor="$configuracao->tiktok_url" :erro="$errors->first('tiktok_url')" placeholder="https://tiktok.com/@..." />
        </fieldset>

        <div class="flex justify-end">
            @can('cms.editar')
                <x-ui.button tipo="submit">Salvar</x-ui.button>
            @endcan
        </div>
    </form>
</x-layouts.admin>
