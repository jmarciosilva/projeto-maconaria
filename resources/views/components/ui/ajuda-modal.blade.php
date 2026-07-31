@props(['titulo', 'itens' => []])

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

        <div class="relative mx-auto mt-16 max-w-xl rounded-lg bg-white p-6 shadow-xl">
            <div class="flex items-start justify-between gap-4">
                <h2 class="text-lg font-semibold text-gray-900">{{ $titulo }}</h2>
                <button type="button" @click="ajudaAberta = false" class="text-gray-400 hover:text-gray-600" aria-label="Fechar ajuda">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <ul class="mt-4 space-y-2.5 text-sm text-gray-600">
                @foreach ($itens as $item)
                    <li class="flex gap-2.5">
                        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-blue-800"></span>
                        <span>{{ $item }}</span>
                    </li>
                @endforeach
            </ul>

            <div class="mt-6 flex justify-end">
                <x-ui.button variante="secundario" tipo="button" @click="ajudaAberta = false">Fechar</x-ui.button>
            </div>
        </div>
    </div>
</div>
