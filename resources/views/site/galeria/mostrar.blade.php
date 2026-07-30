<x-layouts.site :titulo="$album->titulo" :meta-descricao="$album->descricao">
    <section class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
        <a href="{{ route('galeria.index') }}" class="text-sm font-semibold text-blue-800 hover:underline">&larr; Voltar para a galeria</a>

        <div class="mb-8 mt-4">
            <h1 class="text-3xl font-bold text-gray-900">{{ $album->titulo }}</h1>
            @if ($album->descricao)
                <p class="mt-2 text-gray-600">{{ $album->descricao }}</p>
            @endif
        </div>

        @if ($album->fotografias->isEmpty())
            <x-ui.empty-state titulo="Nenhuma fotografia neste álbum" descricao="As fotografias aparecerão aqui assim que forem publicadas." />
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($album->fotografias as $fotografia)
                    <figure class="overflow-hidden rounded-md border border-gray-200 bg-white shadow-sm">
                        <img src="{{ Storage::url($fotografia->caminho) }}" alt="{{ $fotografia->texto_alternativo }}" class="aspect-square w-full object-cover">
                        @if ($fotografia->titulo)
                            <figcaption class="p-3 text-sm text-gray-600">{{ $fotografia->titulo }}</figcaption>
                        @endif
                    </figure>
                @endforeach
            </div>
        @endif
    </section>
</x-layouts.site>
