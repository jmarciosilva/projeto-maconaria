@props(['titulo', 'resumo' => null, 'itens' => [], 'exemplos' => []])

<div x-data="{ ajudaAberta: false }" class="inline">
    <button
        type="button"
        @click="ajudaAberta = true"
        class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1.5 text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900"
        aria-label="Abrir ajuda desta tela"
    >
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 17.25h.007v.008H12v-.008Z" />
        </svg>
        <span class="hidden sm:inline">Ajuda</span>
    </button>

    <div x-show="ajudaAberta" x-cloak class="fixed inset-0 z-50 overflow-y-auto px-4 py-6">
        <div class="fixed inset-0 bg-gray-500/75" @click="ajudaAberta = false"></div>

        <div class="relative mx-auto mt-10 flex max-h-[80vh] w-full max-w-2xl flex-col overflow-hidden rounded-xl bg-white shadow-2xl">
            <div class="flex items-start justify-between gap-4 border-b border-gray-100 bg-gradient-to-br from-blue-50 to-white px-6 py-5">
                <div class="flex items-start gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-800 text-white">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 17.25h.007v.008H12v-.008Z" />
                        </svg>
                    </span>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Ajuda desta tela</p>
                        <h2 class="text-lg font-bold text-gray-900">{{ $titulo }}</h2>
                    </div>
                </div>
                <button type="button" @click="ajudaAberta = false" class="shrink-0 text-gray-400 hover:text-gray-600" aria-label="Fechar ajuda">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="overflow-y-auto px-6 py-5">
                @if ($resumo)
                    <p class="text-sm leading-relaxed text-gray-600">{{ $resumo }}</p>
                @endif

                @if (! empty($itens))
                    <ol class="mt-4 space-y-4">
                        @foreach ($itens as $indice => $item)
                            <li class="flex gap-3">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-800">{{ $indice + 1 }}</span>
                                <span class="pt-0.5 text-sm leading-relaxed text-gray-700">{{ $item }}</span>
                            </li>
                        @endforeach
                    </ol>
                @endif

                @if (! empty($exemplos))
                    <div class="mt-5 rounded-lg border border-amber-200 bg-amber-50 p-4">
                        <p class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wide text-amber-800">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 0 0 1.5-.189m-1.5.189a6.01 6.01 0 0 1-1.5-.189m3.75 7.478a12.06 12.06 0 0 1-4.5 0m3.75 2.383a14.406 14.406 0 0 1-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 1 0-7.517 0c.85.493 1.509 1.333 1.509 2.316V18" />
                            </svg>
                            Exemplos práticos
                        </p>
                        <ul class="mt-2.5 space-y-2">
                            @foreach ($exemplos as $exemplo)
                                <li class="text-sm leading-relaxed text-amber-900">{{ $exemplo }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <div class="flex justify-end border-t border-gray-100 px-6 py-4">
                <x-ui.button variante="secundario" tipo="button" @click="ajudaAberta = false">Fechar</x-ui.button>
            </div>
        </div>
    </div>
</div>
