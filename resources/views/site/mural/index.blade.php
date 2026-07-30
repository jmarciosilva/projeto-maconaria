<x-layouts.site titulo="Mural da Loja" meta-descricao="Publicações públicas da Loja, com comentários moderados e reações da comunidade.">
    <section class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Mural da Loja</h1>
            <p class="mt-2 text-gray-600">Publicações públicas da Loja. Faça login para comentar ou curtir.</p>
        </div>

        @if ($publicacoes->isEmpty())
            <x-ui.empty-state titulo="Nenhuma publicação pública no momento" descricao="Novas publicações aparecerão aqui." />
        @else
            <div class="space-y-5">
                @foreach ($publicacoes as $publicacao)
                    <article class="rounded-md border border-gray-200 bg-white p-5 shadow-sm">
                        <div class="mb-4 flex items-start gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-950 text-sm font-semibold text-white">
                                {{ mb_substr($publicacao->autor->name ?? config('app.name'), 0, 1) }}
                            </div>
                            <div>
                                <h2 class="text-base font-semibold text-gray-900">
                                    <a href="{{ route('mural.mostrar', $publicacao) }}" class="hover:text-blue-800">{{ $publicacao->titulo }}</a>
                                </h2>
                                <p class="text-xs text-gray-500">{{ $publicacao->autor->name ?? 'Administração da Loja' }} · {{ $publicacao->publicado_em?->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>

                        <p class="whitespace-pre-line text-sm text-gray-700">{{ $publicacao->conteudo }}</p>

                        <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-3 text-sm text-gray-600">
                            <span>{{ $publicacao->reacoes_count }} curtida(s)</span>
                            <a href="{{ route('mural.mostrar', $publicacao) }}" class="hover:text-blue-800">{{ $publicacao->comentarios_aprovados_count }} comentário(s)</a>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-6">{{ $publicacoes->links() }}</div>
        @endif
    </section>
</x-layouts.site>
