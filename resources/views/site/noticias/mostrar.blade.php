<x-layouts.site :titulo="$noticia->titulo" :meta-descricao="$noticia->resumo">
    <article class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
        @if ($noticia->categoria)
            <p class="text-sm font-semibold uppercase tracking-wide text-blue-800">{{ $noticia->categoria->nome }}</p>
        @endif

        <h1 class="mt-2 text-3xl font-bold text-gray-900">{{ $noticia->titulo }}</h1>

        <p class="mt-3 text-sm text-gray-500">
            Publicado em {{ optional($noticia->publicado_em)->format('d/m/Y H:i') }}
        </p>

        @if ($noticia->imagem_capa)
            <img src="{{ Storage::url($noticia->imagem_capa) }}" alt="{{ $noticia->titulo }}" class="mt-6 aspect-video w-full rounded-lg object-cover">
        @endif

        @if ($noticia->resumo)
            <p class="mt-6 text-lg text-gray-700">{{ $noticia->resumo }}</p>
        @endif

        <div class="prose prose-blue mt-8 max-w-none">
            {!! $noticia->conteudo !!}
        </div>

        @if ($noticia->fotos->isNotEmpty())
            <section class="mt-8">
                <h2 class="mb-4 text-xl font-semibold text-gray-900">Fotografias</h2>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($noticia->fotos as $foto)
                        <div class="overflow-hidden rounded-lg">
                            <div class="aspect-square w-full rounded-lg bg-gray-50">
                                <img src="{{ Storage::url($foto->caminho) }}"
                                     alt="{{ $foto->descricao ?: 'Foto da notícia: ' . $noticia->titulo }}"
                                     class="h-full w-full object-contain object-center"
                                     loading="lazy">
                            </div>
                            @if ($foto->descricao)
                                <p class="mt-2 px-2 pb-2 text-sm text-gray-600">{{ $foto->descricao }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($noticia->tags->isNotEmpty())
            <div class="mt-8 flex flex-wrap gap-2">
                @foreach ($noticia->tags as $tag)
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">{{ $tag->nome }}</span>
                @endforeach
            </div>
        @endif
    </article>
</x-layouts.site>
