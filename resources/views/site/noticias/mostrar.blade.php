<x-layouts.site :titulo="$noticia->titulo" :meta-descricao="$noticia->resumo">
    <article class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
        @if ($noticia->categoria)
            <p class="text-sm font-semibold uppercase tracking-wide text-blue-800">{{ $noticia->categoria->nome }}</p>
        @endif

        <h1 class="mt-2 text-3xl font-bold text-gray-900">{{ $noticia->titulo }}</h1>

        <p class="mt-3 text-sm text-gray-500">
            Publicado em {{ optional($noticia->publicado_em)->format('d/m/Y H:i') }}
        </p>

        @if ($noticia->resumo)
            <p class="mt-6 text-lg text-gray-700">{{ $noticia->resumo }}</p>
        @endif

        <div class="prose prose-blue mt-8 max-w-none">
            {!! $noticia->conteudo !!}
        </div>

        @if ($noticia->tags->isNotEmpty())
            <div class="mt-8 flex flex-wrap gap-2">
                @foreach ($noticia->tags as $tag)
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">{{ $tag->nome }}</span>
                @endforeach
            </div>
        @endif
    </article>
</x-layouts.site>
