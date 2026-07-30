<x-layouts.site titulo="Notícias" meta-descricao="Notícias públicas e comunicados institucionais.">
    <section class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Notícias</h1>
            <p class="mt-2 text-gray-600">Acompanhe as publicações públicas da Loja.</p>
        </div>

        @if ($noticias->isEmpty())
            <x-ui.empty-state titulo="Nenhuma notícia publicada" descricao="Novas publicações aparecerão aqui." />
        @else
            <div class="grid gap-4 md:grid-cols-3">
                @foreach ($noticias as $noticia)
                    <a href="{{ route('noticias.mostrar', $noticia->slug) }}" class="rounded-md border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md">
                        @if ($noticia->categoria)
                            <p class="text-xs font-semibold uppercase tracking-wide text-blue-800">{{ $noticia->categoria->nome }}</p>
                        @endif

                        <h2 class="mt-2 text-lg font-semibold text-gray-900">{{ $noticia->titulo }}</h2>

                        @if ($noticia->resumo)
                            <p class="mt-2 line-clamp-3 text-sm text-gray-600">{{ $noticia->resumo }}</p>
                        @endif

                        <p class="mt-4 text-xs text-gray-500">{{ optional($noticia->publicado_em)->format('d/m/Y') }}</p>
                    </a>
                @endforeach
            </div>

            <div class="mt-6">{{ $noticias->links() }}</div>
        @endif
    </section>
</x-layouts.site>
