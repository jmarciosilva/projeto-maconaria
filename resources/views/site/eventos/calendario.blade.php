<x-layouts.site titulo="Calendário" meta-descricao="Calendário público de eventos e sessões da Loja.">
    <section class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Calendário</h1>
                <p class="mt-2 text-gray-600">Eventos e sessões entre {{ $inicio->format('d/m/Y') }} e {{ $fim->format('d/m/Y') }}.</p>
            </div>

            <a href="{{ route('eventos.index') }}" class="text-sm font-semibold text-blue-800 hover:underline">Ver lista de eventos</a>
        </div>

        @if ($eventos->isEmpty())
            <x-ui.empty-state titulo="Nenhum evento público no período" descricao="Novos eventos aparecerão aqui." />
        @else
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($eventos as $dia => $eventosDoDia)
                    <section class="rounded-md border border-gray-200 bg-white p-4 shadow-sm">
                        <h2 class="font-semibold text-gray-900">{{ \Illuminate\Support\Carbon::parse($dia)->format('d/m/Y') }}</h2>

                        <div class="mt-3 space-y-3">
                            @foreach ($eventosDoDia as $evento)
                                <a href="{{ route('eventos.mostrar', $evento->slug) }}" class="block rounded-md border border-gray-100 p-3 hover:border-blue-200 hover:bg-blue-50">
                                    <p class="text-sm font-semibold text-gray-900">{{ $evento->inicio_em->format('H:i') }} — {{ $evento->titulo }}</p>
                                    <p class="mt-1 text-xs text-gray-600">{{ $evento->tipo->rotulo() }}</p>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        @endif
    </section>
</x-layouts.site>
