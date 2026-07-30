<x-layouts.restrito>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">{{ $evento->titulo }}</h2>
    </x-slot>

    @php
        $confirmacao = $evento->confirmacoes->first();
    @endphp

    <article class="rounded-md border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-blue-800">{{ $evento->tipo->rotulo() }}</p>
                <h1 class="mt-1 text-2xl font-bold text-gray-900">{{ $evento->titulo }}</h1>
            </div>

            <x-ui.badge :tipo="$evento->visibilidade->value === 'restrita' ? 'aviso' : 'neutro'">{{ $evento->visibilidade->rotulo() }}</x-ui.badge>
        </div>

        <dl class="mt-6 grid gap-4 rounded-md bg-gray-50 p-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs font-semibold uppercase text-gray-500">Início</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $evento->inicio_em->format('d/m/Y H:i') }}</dd>
            </div>

            @if ($evento->fim_em)
                <div>
                    <dt class="text-xs font-semibold uppercase text-gray-500">Término</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $evento->fim_em->format('d/m/Y H:i') }}</dd>
                </div>
            @endif

            @if ($evento->local)
                <div>
                    <dt class="text-xs font-semibold uppercase text-gray-500">Local</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $evento->local }}</dd>
                </div>
            @endif

            @if ($evento->inscricoes_ate)
                <div>
                    <dt class="text-xs font-semibold uppercase text-gray-500">Confirmações até</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $evento->inscricoes_ate->format('d/m/Y H:i') }}</dd>
                </div>
            @endif
        </dl>

        @if ($evento->descricao)
            <p class="mt-6 whitespace-pre-line text-gray-700">{{ $evento->descricao }}</p>
        @endif

        <div class="mt-6 border-t border-gray-200 pt-6">
            @if ($confirmacao?->status->value === 'confirmado')
                <div class="flex flex-wrap items-center gap-3">
                    <x-ui.badge tipo="sucesso">Presença confirmada</x-ui.badge>

                    <form method="POST" action="{{ route('area-restrita.eventos.cancelar-confirmacao', $evento) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-sm font-semibold text-red-700 hover:underline">Cancelar confirmação</button>
                    </form>
                </div>
            @elseif ($evento->aceitaConfirmacao())
                <form method="POST" action="{{ route('area-restrita.eventos.confirmar', $evento) }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="observacao" class="block text-sm font-medium text-gray-700">Observação</label>
                        <textarea id="observacao" name="observacao" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">{{ old('observacao') }}</textarea>
                        @error('observacao')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-ui.button tipo="submit">Confirmar presença</x-ui.button>
                </form>
            @elseif ($evento->permite_confirmacao)
                <x-ui.alert tipo="aviso">Este evento não aceita confirmação de presença no momento.</x-ui.alert>
            @endif
        </div>
    </article>
</x-layouts.restrito>
