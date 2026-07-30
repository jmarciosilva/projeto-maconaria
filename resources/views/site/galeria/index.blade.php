<x-layouts.site titulo="Galeria da Loja" meta-descricao="Registros públicos de eventos, sessões e momentos institucionais da Loja.">
    <section class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Galeria da Loja</h1>
            <p class="mt-2 text-gray-600">Registros públicos de eventos, sessões e momentos institucionais.</p>
        </div>

        @if ($albuns->isEmpty())
            <x-ui.empty-state titulo="Nenhum álbum público no momento" descricao="Novos álbuns aparecerão aqui." />
        @else
            <div class="grid gap-5 md:grid-cols-3">
                @foreach ($albuns as $album)
                    @php($fotoPrincipal = $album->fotografias->first())
                    <a href="{{ route('galeria.mostrar', $album->slug) }}" class="overflow-hidden rounded-md border border-gray-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md">
                        @if ($fotoPrincipal)
                            <img src="{{ Storage::url($fotoPrincipal->caminho) }}" alt="{{ $fotoPrincipal->texto_alternativo }}" class="aspect-video w-full object-cover">
                        @else
                            <div class="flex aspect-video w-full items-center justify-center bg-blue-950 text-sm font-semibold text-white">Galeria</div>
                        @endif
                        <div class="p-5">
                            <h2 class="text-base font-semibold text-gray-900">{{ $album->titulo }}</h2>
                            @if ($album->descricao)
                                <p class="mt-2 line-clamp-3 text-sm text-gray-600">{{ $album->descricao }}</p>
                            @endif
                            <p class="mt-3 text-sm text-gray-500">{{ $album->fotografias_count }} fotografia(s)</p>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-6">{{ $albuns->links() }}</div>
        @endif
    </section>
</x-layouts.site>
