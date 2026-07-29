@props([
    'acao',
    'metodo' => 'POST',
    'titulo' => 'Confirmar ação',
    'mensagem' => 'Tem certeza que deseja continuar? Esta ação pode não ser reversível.',
    'rotulo' => 'Confirmar',
])

<div x-data="{ aberto: false }" class="inline">
    <span @click="aberto = true">{{ $gatilho }}</span>

    <div x-show="aberto" x-cloak class="fixed inset-0 z-50 overflow-y-auto px-4 py-6">
        <div class="fixed inset-0 bg-gray-500/75" @click="aberto = false"></div>

        <div class="relative mx-auto mt-16 max-w-md rounded-lg bg-white p-6 shadow-xl">
            <h2 class="text-lg font-semibold text-gray-900">{{ $titulo }}</h2>
            <p class="mt-2 text-sm text-gray-600">{{ $mensagem }}</p>

            <form method="POST" action="{{ $acao }}" class="mt-6 flex justify-end gap-3">
                @csrf
                @if (strtoupper($metodo) !== 'POST')
                    @method($metodo)
                @endif

                <x-ui.button variante="secundario" tipo="button" @click="aberto = false">Cancelar</x-ui.button>
                <x-ui.button variante="perigo" tipo="submit">{{ $rotulo }}</x-ui.button>
            </form>
        </div>
    </div>
</div>
