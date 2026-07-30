<x-layouts.restrito>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Painel</h2>
    </x-slot>

    <div class="rounded-lg bg-white p-6 shadow-sm">
        <p class="text-gray-700">
            Bem-vindo(a), {{ auth()->user()->name }}. Esta é a área restrita aos usuários
            autenticados e Irmãos da Loja.
        </p>
    </div>

    <section class="mt-6 rounded-lg bg-white p-6 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Próximos eventos e sessões</h3>
            <a href="{{ route('area-restrita.eventos.index') }}" class="text-sm font-semibold text-blue-800 hover:underline">Ver agenda</a>
        </div>

        @if ($proximosEventos->isEmpty())
            <p class="text-sm text-gray-600">Nenhum evento previsto no momento.</p>
        @else
            <div class="space-y-3">
                @foreach ($proximosEventos as $evento)
                    <a href="{{ route('area-restrita.eventos.mostrar', $evento) }}" class="block rounded-md border border-gray-200 p-4 hover:border-blue-200 hover:bg-blue-50">
                        <p class="text-sm font-semibold text-gray-900">{{ $evento->inicio_em->format('d/m/Y H:i') }} — {{ $evento->titulo }}</p>
                        <p class="mt-1 text-xs text-gray-600">{{ $evento->tipo->rotulo() }} · {{ $evento->visibilidade->rotulo() }}</p>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
</x-layouts.restrito>
