<x-layouts.admin titulo="Chancelaria">
    <header class="mb-6">
        <h1 class="text-xl font-bold text-gray-900">Chancelaria</h1>
        <p class="mt-1 text-sm text-gray-500">Gestão das sessões, presenças e frequência dos Irmãos.</p>
    </header>

    {{-- Fluxo principal: Sessões → Frequência → Relatório --}}
    <div class="mb-8 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        @can('chancelaria.criar')
            <a href="{{ route('admin.chancelaria.sessoes.create') }}"
               class="flex items-center gap-3 rounded-lg border border-transparent bg-[#14213D] px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1B2A4A]">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Nova sessão
            </a>
        @endcan

        <a href="{{ route('admin.chancelaria.frequencias.selecionar-evento') }}"
           class="flex items-center gap-3 rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">
            <svg class="h-5 w-5 shrink-0 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            Registrar frequência
        </a>

        <a href="{{ route('admin.chancelaria.relatorios.frequencia') }}"
           class="flex items-center gap-3 rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">
            <svg class="h-5 w-5 shrink-0 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
            </svg>
            Relatório de frequência
        </a>
    </div>

    {{-- Resumo compacto, com período explícito --}}
    <section class="mb-8">
        <h2 class="text-sm font-semibold text-gray-900">
            Resumo dos últimos 3 meses
            <span class="font-normal text-gray-500">({{ $inicio->format('d/m/Y') }} a {{ $fim->format('d/m/Y') }})</span>
        </h2>

        <div class="mt-3 grid gap-3 grid-cols-2 lg:grid-cols-4">
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <p class="text-sm text-gray-600">Sessões</p>
                <p class="mt-1 text-2xl font-bold text-gray-900">{{ $sessoesNoPeriodo }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <p class="text-sm text-gray-600">Presenças</p>
                <p class="mt-1 text-2xl font-bold text-green-700">{{ $presentes }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <p class="text-sm text-gray-600">Ausências</p>
                <p class="mt-1 text-2xl font-bold text-red-700">{{ $ausentes }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <p class="text-sm text-gray-600">Justificativas</p>
                <p class="mt-1 text-2xl font-bold text-amber-700">{{ $justificados }}</p>
            </div>
        </div>

        <p class="mt-2 text-xs text-gray-500">
            Sessões: realizadas no período. Presenças, ausências e justificativas: lançamentos
            registrados no período — o lançamento histórico de sessões antigas conta na data em que foi feito.
        </p>
    </section>

    {{-- Sessões recentes: exclusivamente tipo=SESSAO --}}
    <section class="mb-8">
        <div class="mb-3 flex items-center justify-between gap-3">
            <h2 class="text-base font-semibold text-gray-900">Sessões recentes</h2>
            <a href="{{ route('admin.chancelaria.sessoes.index') }}" class="text-sm font-semibold text-blue-800 hover:underline">Ver todas</a>
        </div>

        @if ($sessoesRecentes->isEmpty())
            <x-ui.empty-state titulo="Nenhuma sessão cadastrada" descricao="Cadastre a primeira sessão para começar o lançamento da frequência." />
        @else
            <ul class="divide-y divide-gray-100 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                @foreach ($sessoesRecentes as $sessao)
                    <li class="flex flex-col gap-2 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900">
                                {{ $sessao->inicio_em->format('d/m/Y') }}
                                <span class="font-normal text-gray-500">&middot; {{ $sessao->inicio_em->format('H:i') }}</span>
                            </p>
                            <p class="mt-0.5 truncate text-sm text-gray-600">
                                {{ $sessao->titulo }}
                                @if ($sessao->sessao_classe)
                                    <span class="text-gray-400">&middot;</span> {{ $sessao->sessao_classe->rotulo() }}
                                @endif
                            </p>
                        </div>

                        <div class="flex shrink-0 items-center gap-3">
                            @if ($sessao->frequencias_count > 0)
                                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-800">
                                    Frequência registrada
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-800">
                                    Frequência pendente
                                </span>
                            @endif

                            @can('chancelaria.editar')
                                <a href="{{ route('admin.chancelaria.frequencias.edit', $sessao) }}" class="text-sm font-medium text-blue-800 hover:underline">
                                    Lançar
                                </a>
                            @endcan
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    {{-- Funções secundárias, sem competir com o fluxo principal --}}
    <section>
        <h2 class="mb-3 text-sm font-semibold text-gray-900">Outras funções</h2>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.chancelaria.visitantes.index') }}"><x-ui.button variante="secundario">Visitantes</x-ui.button></a>
            <a href="{{ route('admin.chancelaria.comunicados.index') }}"><x-ui.button variante="secundario">Comunicados</x-ui.button></a>
        </div>
    </section>
</x-layouts.admin>
