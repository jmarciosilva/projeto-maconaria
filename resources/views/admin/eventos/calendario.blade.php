<x-layouts.admin titulo="Calendário de Eventos">
    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-gray-600">Eventos entre {{ $inicio->format('d/m/Y') }} e {{ $fim->format('d/m/Y') }}.</p>

        <a href="{{ route('admin.eventos.index') }}">
            <x-ui.button variante="secundario">Lista de eventos</x-ui.button>
        </a>
    </div>

    @if ($eventos->isEmpty())
        <x-ui.empty-state titulo="Nenhum evento no período" descricao="Cadastre eventos para visualizá-los no calendário." />
    @else
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($eventos as $dia => $eventosDoDia)
                <section class="rounded-md border border-gray-200 bg-white p-4 shadow-sm">
                    <h2 class="font-semibold text-gray-900">{{ \Illuminate\Support\Carbon::parse($dia)->format('d/m/Y') }}</h2>

                    <div class="mt-3 space-y-3">
                        @foreach ($eventosDoDia as $evento)
                            <a href="{{ route('admin.eventos.edit', $evento) }}" class="block rounded-md border border-gray-100 p-3 hover:border-blue-200 hover:bg-blue-50">
                                <p class="text-sm font-semibold text-gray-900">{{ $evento->inicio_em->format('H:i') }} — {{ $evento->titulo }}</p>
                                <p class="mt-1 text-xs text-gray-600">{{ $evento->tipo->rotulo() }} · {{ $evento->visibilidade->rotulo() }}</p>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    @endif
</x-layouts.admin>
