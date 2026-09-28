@php
$noticia ??= null;
$tagsSelecionadas = collect(old('tags', $noticia?->tags->pluck('id')->all() ?? []))->map(fn ($id) => (int) $id)->all();
@endphp

@if ($errors->any())
    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4">
        <div class="flex gap-3">
            <svg class="h-5 w-5 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c.866-1.5 2.945-2.915 5.304-3.917.520-.265 1.209-.42 1.896-.436a7.5 7.5 0 1 1-5.898 3.75M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            </svg>
            <div>
                <h3 class="text-sm font-semibold text-red-800">Erros ao salvar a notícia</h3>
                <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-red-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif

<section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
    <div class="mb-5 flex items-start gap-3">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-700">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
            </svg>
        </span>
        <div>
            <h2 class="text-base font-semibold text-gray-900">Identificação</h2>
            <p class="mt-0.5 text-sm text-gray-500">Título exibido na notícia e o endereço (URL) dela no site.</p>
        </div>
    </div>

    <div class="space-y-4" x-data="{ titulo: @json(old('titulo', $noticia->titulo ?? '')), slug: @json(old('slug', $noticia->slug ?? '')) }">
        <div>
            <x-ui.input rotulo="Título" nome="titulo" :valor="old('titulo', $noticia->titulo ?? null)" :erro="$errors->first('titulo')" obrigatorio @input="titulo = $event.target.value" />
            <p class="mt-1.5 text-xs text-gray-500">
                <span x-text="titulo.length"></span> de 255 caracteres
            </p>
        </div>

        <div>
            <x-ui.input
                rotulo="Slug"
                nome="slug"
                :valor="old('slug', $noticia->slug ?? null)"
                :erro="$errors->first('slug')"
                obrigatorio
                placeholder="ex.: comunicado-da-semana"
                @input="slug = $event.target.value"
            />
            <p class="mt-1.5 text-xs text-gray-500">
                URL amigável da notícia. Use apenas letras minúsculas, números e hífens.
            </p>
            @if ($errors->has('slug'))
                <p class="mt-1 text-sm text-red-600">
                    <strong>Dica:</strong> Se o slug "já existe", tente adicionar a data (ex.: comunicado-setembro-2026)
                </p>
            @endif
        </div>
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
            <p class="mt-0.5 text-sm text-gray-500">Categoria, status e quando esta notícia fica visível.</p>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2" x-data="{ status: @json(old('status', $noticia->status->value ?? 'rascunho')) }">
        <x-ui.select rotulo="Categoria" nome="categoria_id" :opcoes="['' => 'Sem categoria'] + $categorias->all()" :valor="$noticia->categoria_id ?? null" :erro="$errors->first('categoria_id')" />

        <div>
            <x-ui.select rotulo="Status" nome="status" :opcoes="$statusDisponiveis->all()" :valor="$noticia->status->value ?? 'rascunho'" :erro="$errors->first('status')" obrigatorio @change="status = $event.target.value" />
            <p class="mt-1.5 text-xs text-gray-500">
                <strong>Dica:</strong>
                <template x-if="status === 'rascunho'">Use <span class="font-medium">Rascunho</span> enquanto escreve</template>
                <template x-if="status === 'publicada'">Use <span class="font-medium">Publicada</span> para exibir no site agora</template>
                <template x-if="status === 'agendada'">Use <span class="font-medium">Agendada</span> para agendar uma data futura</template>
            </p>
            @if ($errors->has('status'))
                <p class="mt-1 text-sm text-red-600">
                    <strong>Erro:</strong> {{ $errors->first('status') }}
                </p>
            @endif
        </div>

        <x-ui.select
            rotulo="Visibilidade"
            nome="visibilidade"
            :opcoes="['publica' => 'Pública', 'restrita' => 'Restrita']"
            :valor="$noticia->visibilidade->value ?? 'publica'"
            :erro="$errors->first('visibilidade')"
            obrigatorio
        />

        <div x-show="status === 'publicada'">
            <x-ui.input rotulo="Publicado em" nome="publicado_em" tipo="datetime-local" :valor="old('publicado_em', isset($noticia?->publicado_em) ? $noticia->publicado_em->format('Y-m-d\TH:i') : null)" :erro="$errors->first('publicado_em')" />
            <p class="mt-1.5 text-xs text-gray-500">Se deixar em branco, usará a data de agora</p>
        </div>

        <div x-show="status === 'agendada'">
            <x-ui.input rotulo="Agendado para" nome="agendado_para" tipo="datetime-local" :valor="old('agendado_para', isset($noticia?->agendado_para) ? $noticia->agendado_para->format('Y-m-d\TH:i') : null)" :erro="$errors->first('agendado_para')" />
            <p class="mt-1.5 text-xs text-gray-500">Obrigatório para notícias agendadas</p>
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
            <h2 class="text-base font-semibold text-gray-900">Capa e resumo</h2>
            <p class="mt-0.5 text-sm text-gray-500">Imagem e texto curto exibidos nas listagens de notícias e na home.</p>
        </div>
    </div>

    <div class="space-y-5">
        <div x-data="{ nomeArquivo: null }">
            <label class="block text-sm font-medium text-gray-700">Imagem de capa</label>

            <div class="mt-1.5 flex aspect-[16/10] w-full max-w-sm items-center justify-center overflow-hidden rounded-md border border-gray-200 bg-gray-50">
                @if (isset($noticia) && $noticia->imagem_capa)
                    <img src="{{ asset('storage/'.$noticia->imagem_capa) }}" alt="Capa atual da notícia" class="h-full w-full object-cover">
                @else
                    <svg class="h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3 12V4.5A1.5 1.5 0 0 1 4.5 3h15A1.5 1.5 0 0 1 21 4.5v15a1.5 1.5 0 0 1-1.5 1.5H4.5A1.5 1.5 0 0 1 3 19.5v-1.875M9 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" /></svg>
                @endif
            </div>

            <label for="imagem_capa" class="mt-2 inline-flex cursor-pointer items-center justify-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                Escolher imagem
            </label>
            <input type="file" id="imagem_capa" name="imagem_capa" accept="image/*" class="sr-only" @change="nomeArquivo = $event.target.files[0]?.name ?? null">
            <p class="mt-1.5 text-xs text-gray-500" x-text="nomeArquivo ?? 'JPG, PNG ou WebP, na horizontal (ideal: proporção 16:9 ou 16:10). Máximo de 4 MB.'"></p>
            @error('imagem_capa')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div x-data="{ resumo: @json(old('resumo', $noticia->resumo ?? '')) }">
            <label for="resumo" class="block text-sm font-medium text-gray-700">Resumo</label>
            <textarea id="resumo" name="resumo" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('resumo') border-red-400 @enderror" @input="resumo = $event.target.value">{{ old('resumo', $noticia->resumo ?? '') }}</textarea>
            <div class="mt-1.5 flex justify-between">
                <p class="text-xs text-gray-500">
                    <span x-text="resumo.length"></span> de 500 caracteres
                </p>
                @if (old('resumo', $noticia->resumo ?? false))
                    <p class="text-xs text-green-600">✓ Resumo preenchido</p>
                @endif
            </div>
            @error('resumo')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
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
            <p class="mt-0.5 text-sm text-gray-500">Texto completo da notícia, com formatação rica.</p>
        </div>
    </div>

    <div id="conteudo-editor" data-quill-editor data-quill-target="conteudo-input" class="min-h-64 rounded-md border border-gray-200 bg-white">{!! old('conteudo', $noticia->conteudo ?? '') !!}</div>
    <textarea name="conteudo" id="conteudo-input" class="hidden">{{ old('conteudo', $noticia->conteudo ?? '') }}</textarea>
    <p class="mt-1.5 text-xs text-gray-500">Você pode usar <strong>Bold</strong>, <em>Itálico</em>, listas e links para formatar seu conteúdo.</p>
    @error('conteudo')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</section>

<section class="mt-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
    <div class="mb-5 flex items-start gap-3">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-rose-100 text-rose-700">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
        </span>
        <div>
            <h2 class="text-base font-semibold text-gray-900">Fotos da notícia</h2>
            <p class="mt-0.5 text-sm text-gray-500">Adicione uma ou mais fotos para ilustrar sua notícia.</p>
        </div>
    </div>

    <div class="space-y-5">
        <div x-data="{ arquivos: @json($noticia?->fotos ?? []), arquivosNovos: [] }">
            @if (isset($noticia) && $noticia->fotos->isNotEmpty())
                <div>
                    <h3 class="mb-3 text-sm font-medium text-gray-700">Fotos atuais</h3>
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($noticia->fotos as $foto)
                            <div class="group relative">
                                <div class="aspect-video overflow-hidden rounded-md border border-gray-200 bg-gray-100">
                                    <img src="{{ asset('storage/'.$foto->caminho) }}" alt="{{ $foto->descricao }}" class="h-full w-full object-cover">
                                </div>
                                <div class="mt-2 flex gap-2">
                                    <input type="text" name="fotos_descricao[{{ $foto->id }}]" value="{{ $foto->descricao }}" placeholder="Descrição (alt text)" class="flex-1 rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <label class="inline-flex cursor-pointer items-center">
                                        <input type="checkbox" name="fotos_para_remover[]" value="{{ $foto->id }}" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                        <span class="ml-2 text-sm text-red-600">Remover</span>
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-gray-700">Adicionar novas fotos</label>
                <div class="mt-1.5 flex flex-col gap-3">
                    <div class="rounded-md border-2 border-dashed border-gray-300 px-6 py-8 text-center">
                        <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3 12V4.5A1.5 1.5 0 0 1 4.5 3h15A1.5 1.5 0 0 1 21 4.5v15a1.5 1.5 0 0 1-1.5 1.5H4.5A1.5 1.5 0 0 1 3 19.5V12Z" />
                        </svg>
                        <p class="mt-2 text-sm text-gray-600">
                            <label for="fotos" class="font-semibold text-blue-600 hover:text-blue-500 cursor-pointer">Clique para adicionar fotos</label>
                            ou arraste aqui
                        </p>
                        <p class="text-xs text-gray-500 mt-1">JPG, PNG ou WebP até 4 MB cada. Máximo 10 fotos.</p>
                    </div>
                    <input type="file" id="fotos" name="fotos[]" accept="image/*" multiple class="sr-only" @change="arquivosNovos = $event.target.files; console.log('Arquivos:', $event.target.files.length)">

                    <div x-show="arquivosNovos.length > 0" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        <template x-for="(arquivo, index) in arquivosNovos" :key="index">
                            <div class="group relative">
                                <div class="aspect-video overflow-hidden rounded-md border border-blue-200 bg-blue-50 flex items-center justify-center">
                                    <span class="text-sm text-blue-600" x-text="arquivo.name"></span>
                                </div>
                                <input type="text" name="fotos_descricao[]" placeholder="Descrição (alt text)" class="mt-2 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </template>
                    </div>
                </div>
                @error('fotos')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>
</section>

<section class="mt-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
    <div class="mb-5 flex items-start gap-3">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-indigo-700">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.169.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
            </svg>
        </span>
        <div>
            <h2 class="text-base font-semibold text-gray-900">Tags e destaque</h2>
            <p class="mt-0.5 text-sm text-gray-500">Organização e destaque desta notícia na página inicial.</p>
        </div>
    </div>

    <div class="space-y-5">
        @if ($tags->isNotEmpty())
            <div>
                <span class="block text-sm font-medium text-gray-700">Tags</span>
                <div class="mt-2 grid gap-2.5 sm:grid-cols-2">
                    @foreach ($tags as $tag)
                        <label class="flex items-center justify-between gap-3 rounded-md border border-gray-200 px-4 py-3">
                            <span class="text-sm font-medium text-gray-700">{{ $tag->nome }}</span>
                            <input type="checkbox" name="tags[]" value="{{ $tag->id }}" @checked(in_array($tag->id, $tagsSelecionadas, true)) class="rounded border-gray-300 text-blue-800 focus:ring-blue-700">
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        <label class="flex items-center justify-between gap-3 rounded-md border border-gray-200 px-4 py-3">
            <span class="text-sm font-medium text-gray-700">Destacar na página inicial</span>
            <input type="checkbox" name="destaque" value="1" @checked(old('destaque', $noticia->destaque ?? false)) class="rounded border-gray-300 text-blue-800 focus:ring-blue-700">
        </label>
    </div>
</section>
