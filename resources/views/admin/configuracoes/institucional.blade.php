<x-layouts.admin titulo="Configurações Institucionais">
    <p class="mb-6 max-w-2xl text-sm text-gray-600">Esses dados alimentam o cabeçalho, o rodapé e a home do site público — alterações aqui refletem imediatamente para os visitantes.</p>

    <form method="POST" action="{{ route('admin.configuracoes.institucional.update') }}" enctype="multipart/form-data" class="max-w-3xl space-y-6">
        @csrf
        @method('PUT')

        <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <div class="mb-5 flex items-start gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                    </svg>
                </span>
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Identidade institucional</h2>
                    <p class="mt-0.5 text-sm text-gray-500">Nome e textos exibidos no cabeçalho e na página inicial do site.</p>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.input rotulo="Nome da Loja" nome="nome_loja" :valor="$configuracao->nome_loja" :erro="$errors->first('nome_loja')" placeholder="{{ config('app.name') }}" />
                <x-ui.input rotulo="Telefone institucional" nome="telefone_institucional" :valor="$configuracao->telefone_institucional" :erro="$errors->first('telefone_institucional')" placeholder="(00) 0000-0000 ou (00) 00000-0000" />
                <div class="sm:col-span-2">
                    <x-ui.input rotulo="Título institucional (exibido na home)" nome="titulo_institucional" :valor="$configuracao->titulo_institucional" :erro="$errors->first('titulo_institucional')" />
                </div>
                <div class="sm:col-span-2">
                    <x-ui.input rotulo="Subtítulo institucional (exibido na home)" nome="subtitulo_institucional" :valor="$configuracao->subtitulo_institucional" :erro="$errors->first('subtitulo_institucional')" />
                </div>
            </div>
        </section>

        <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <div class="mb-5 flex items-start gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-purple-100 text-purple-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3 12V4.5A1.5 1.5 0 0 1 4.5 3h15A1.5 1.5 0 0 1 21 4.5v15a1.5 1.5 0 0 1-1.5 1.5H4.5A1.5 1.5 0 0 1 3 19.5v-1.875M9 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
                    </svg>
                </span>
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Logotipo</h2>
                    <p class="mt-0.5 text-sm text-gray-500">Selo exibido no cabeçalho, rodapé e favicon do site.</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-5" x-data="{ nomeArquivo: null }">
                <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-full border border-gray-200 bg-gray-50">
                    @if ($configuracao->logotipo)
                        <img src="{{ asset('storage/'.$configuracao->logotipo) }}" alt="Logotipo atual da Loja" class="h-full w-full object-contain">
                    @else
                        <svg class="h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3 12V4.5A1.5 1.5 0 0 1 4.5 3h15A1.5 1.5 0 0 1 21 4.5v15a1.5 1.5 0 0 1-1.5 1.5H4.5A1.5 1.5 0 0 1 3 19.5v-1.875M9 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
                        </svg>
                    @endif
                </div>

                <div>
                    <label for="logotipo" class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                        Substituir logotipo
                    </label>
                    <input type="file" id="logotipo" name="logotipo" accept="image/*" class="sr-only" @change="nomeArquivo = $event.target.files[0]?.name ?? null">
                    <p class="mt-1.5 text-xs text-gray-500" x-text="nomeArquivo ?? 'PNG ou JPG, formato quadrado recomendado.'"></p>
                    @error('logotipo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <div class="mb-5 flex items-start gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-green-100 text-green-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                    </svg>
                </span>
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Contato e rodapé</h2>
                    <p class="mt-0.5 text-sm text-gray-500">Endereço e e-mail exibidos no rodapé do site (o endereço também alimenta os links de Waze e Google Maps).</p>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label for="endereco_rodape" class="block text-sm font-medium text-gray-700">Endereço (exibido no rodapé)</label>
                    <textarea id="endereco_rodape" name="endereco_rodape" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">{{ old('endereco_rodape', $configuracao->endereco_rodape) }}</textarea>
                    @error('endereco_rodape')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <x-ui.input rotulo="E-mail institucional" nome="email_institucional" tipo="email" :valor="$configuracao->email_institucional" :erro="$errors->first('email_institucional')" />
            </div>
        </section>

        <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <div class="mb-5 flex items-start gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z" />
                    </svg>
                </span>
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Redes sociais</h2>
                    <p class="mt-0.5 text-sm text-gray-500">Links exibidos como ícones no rodapé do site. Deixe em branco para ocultar uma rede.</p>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.input rotulo="Facebook" nome="facebook_url" tipo="url" :valor="$configuracao->facebook_url" :erro="$errors->first('facebook_url')" placeholder="https://facebook.com/..." />
                <x-ui.input rotulo="Instagram" nome="instagram_url" tipo="url" :valor="$configuracao->instagram_url" :erro="$errors->first('instagram_url')" placeholder="https://instagram.com/..." />
                <x-ui.input rotulo="Twitter / X" nome="twitter_url" tipo="url" :valor="$configuracao->twitter_url" :erro="$errors->first('twitter_url')" placeholder="https://x.com/..." />
                <x-ui.input rotulo="TikTok" nome="tiktok_url" tipo="url" :valor="$configuracao->tiktok_url" :erro="$errors->first('tiktok_url')" placeholder="https://tiktok.com/@..." />
            </div>
        </section>

        <div class="flex justify-end">
            @can('cms.editar')
                <x-ui.button tipo="submit">Salvar alterações</x-ui.button>
            @endcan
        </div>
    </form>
</x-layouts.admin>
