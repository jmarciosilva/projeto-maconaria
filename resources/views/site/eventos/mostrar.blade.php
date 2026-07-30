<x-layouts.site :titulo="$evento->titulo" :meta-descricao="$evento->descricao">
    <article class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
        <p class="text-sm font-semibold uppercase tracking-wide text-blue-800">{{ $evento->tipo->rotulo() }}</p>
        <h1 class="mt-2 text-3xl font-bold text-gray-900">{{ $evento->titulo }}</h1>

        @if ($evento->imagem_capa)
            <img src="{{ Storage::url($evento->imagem_capa) }}" alt="{{ $evento->titulo }}" class="mt-6 aspect-video w-full rounded-lg object-cover">
        @endif

        <dl class="mt-6 grid gap-4 rounded-md border border-gray-200 bg-gray-50 p-4 sm:grid-cols-2">
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
        </dl>

        @if ($evento->descricao)
            <div class="prose prose-blue mt-8 max-w-none">
                <p>{{ $evento->descricao }}</p>
            </div>
        @endif
    </article>
</x-layouts.site>
