<x-layouts.site :titulo="$publicacao->titulo" meta-descricao="Publicação do Mural da Loja.">
    <section class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
        <a href="{{ route('mural.index') }}" class="text-sm font-semibold text-blue-800 hover:underline">&larr; Voltar para o mural</a>

        <article class="mt-4 rounded-md border border-gray-200 bg-white p-6 shadow-sm">
            <div class="mb-4 flex items-start gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-950 text-sm font-semibold text-white">
                    {{ mb_substr($publicacao->autor->name ?? config('app.name'), 0, 1) }}
                </div>
                <div>
                    <h1 class="text-lg font-semibold text-gray-900">{{ $publicacao->titulo }}</h1>
                    <p class="text-xs text-gray-500">{{ $publicacao->autor->name ?? 'Administração da Loja' }} · {{ $publicacao->publicado_em?->format('d/m/Y H:i') }}</p>
                </div>
            </div>

            <p class="whitespace-pre-line text-sm text-gray-700">{{ $publicacao->conteudo }}</p>

            <div class="mt-4 flex items-center justify-between border-y border-gray-100 py-3 text-sm text-gray-600">
                <span>{{ $publicacao->reacoes_count }} curtida(s)</span>
                <span>{{ $publicacao->comentarios->count() }} comentário(s)</span>
            </div>

            @auth
                <form method="POST" action="{{ route('mural.reacoes.store', $publicacao) }}" class="mt-3">
                    @csrf
                    <input type="hidden" name="tipo" value="curtir">
                    <button type="submit" class="w-full rounded-md border border-blue-200 px-3 py-2 text-sm font-semibold text-blue-800 hover:bg-blue-50">Curtir</button>
                </form>

                <form method="POST" action="{{ route('mural.comentarios.store', $publicacao) }}" class="mt-4 space-y-2">
                    @csrf
                    <label for="comentario" class="block text-sm font-medium text-gray-700">Deixe um comentário</label>
                    <textarea id="comentario" name="comentario" rows="3" required maxlength="1000" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"></textarea>
                    <x-ui.button tipo="submit">Comentar</x-ui.button>
                </form>
            @else
                <p class="mt-3 text-sm text-gray-600">
                    <a href="{{ route('login') }}" class="font-semibold text-blue-800 hover:underline">Faça login</a> para comentar ou curtir esta publicação.
                </p>
            @endauth
        </article>

        @if ($publicacao->comentarios->isNotEmpty())
            <div class="mt-6 space-y-4">
                <h2 class="text-lg font-semibold text-gray-900">Comentários</h2>

                @foreach ($publicacao->comentarios as $comentario)
                    <div class="rounded-md border border-gray-200 bg-white p-4 shadow-sm">
                        <p class="text-sm font-semibold text-gray-900">{{ $comentario->usuario->name ?? 'Usuário' }}</p>
                        <p class="mt-1 text-sm text-gray-700">{{ $comentario->comentario }}</p>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</x-layouts.site>
