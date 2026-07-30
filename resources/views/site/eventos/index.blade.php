<x-layouts.site titulo="Eventos" meta-descricao="Eventos públicos da Loja.">
    <section class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Eventos</h1>
            <p class="mt-2 text-gray-600">Agenda pública de eventos institucionais.</p>
        </div>

        @if ($eventos->isEmpty())
            <x-ui.empty-state titulo="Nenhum evento público previsto" descricao="Novos eventos aparecerão aqui." />
        @else
            <div class="grid gap-4 md:grid-cols-3">
                @foreach ($eventos as $evento)
                    <a href="{{ route('eventos.mostrar', $evento->slug) }}" class="rounded-md border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md">
                        <p class="text-xs font-semibold uppercase tracking-wide text-blue-800">{{ $evento->tipo->rotulo() }}</p>
                        <h2 class="mt-2 text-lg font-semibold text-gray-900">{{ $evento->titulo }}</h2>
                        <p class="mt-2 text-sm text-gray-600">{{ $evento->inicio_em->format('d/m/Y H:i') }}</p>

                        @if ($evento->local)
                            <p class="mt-1 text-sm text-gray-600">{{ $evento->local }}</p>
                        @endif

                        @if ($evento->descricao)
                            <p class="mt-3 line-clamp-3 text-sm text-gray-600">{{ $evento->descricao }}</p>
                        @endif
                    </a>
                @endforeach
            </div>

            <div class="mt-6">{{ $eventos->links() }}</div>
        @endif
    </section>
</x-layouts.site>
