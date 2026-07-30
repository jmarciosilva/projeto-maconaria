<x-layouts.restrito>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Agenda</h2>
    </x-slot>

    @if ($eventos->isEmpty())
        <x-ui.empty-state titulo="Nenhum evento previsto" descricao="Eventos e sessões publicados aparecerão aqui." />
    @else
        <div class="grid gap-4 md:grid-cols-2">
            @foreach ($eventos as $evento)
                @php
                    $confirmacao = $evento->confirmacoes->first();
                @endphp

                <article class="rounded-md border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-blue-800">{{ $evento->tipo->rotulo() }}</p>
                            <h3 class="mt-1 text-lg font-semibold text-gray-900">{{ $evento->titulo }}</h3>
                        </div>

                        <x-ui.badge :tipo="$evento->visibilidade->value === 'restrita' ? 'aviso' : 'neutro'">{{ $evento->visibilidade->rotulo() }}</x-ui.badge>
                    </div>

                    <p class="mt-3 text-sm text-gray-600">{{ $evento->inicio_em->format('d/m/Y H:i') }}</p>

                    @if ($evento->local)
                        <p class="mt-1 text-sm text-gray-600">{{ $evento->local }}</p>
                    @endif

                    <div class="mt-4 flex flex-wrap items-center gap-3">
                        <a href="{{ route('area-restrita.eventos.mostrar', $evento) }}" class="text-sm font-semibold text-blue-800 hover:underline">Ver detalhes</a>

                        @if ($confirmacao?->status->value === 'confirmado')
                            <x-ui.badge tipo="sucesso">Presença confirmada</x-ui.badge>
                        @elseif ($evento->aceitaConfirmacao())
                            <form method="POST" action="{{ route('area-restrita.eventos.confirmar', $evento) }}">
                                @csrf
                                <button type="submit" class="text-sm font-semibold text-green-700 hover:underline">Confirmar presença</button>
                            </form>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-6">{{ $eventos->links() }}</div>
    @endif
</x-layouts.restrito>
