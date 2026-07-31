<x-layouts.site titulo="Mural da Loja" meta-descricao="Publicações públicas da Loja, com comentários moderados e reações da comunidade.">
    <section class="border-b border-brand-navy/10 bg-brand-paperSoft py-10 lg:py-12">
        <div class="mx-auto max-w-2xl px-5 lg:px-8">
            <h1 class="font-siteDisplay text-3xl font-bold text-brand-navy sm:text-4xl">Mural da Loja</h1>
            <p class="mt-2 text-lg text-brand-inkSoft">Publicações públicas da Loja. Faça login para comentar ou curtir.</p>
        </div>
    </section>

    <section class="mx-auto max-w-2xl px-5 py-10 lg:px-8 lg:py-14">
        @if ($publicacoes->isEmpty())
            <x-ui.empty-state titulo="Nenhuma publicação pública no momento" descricao="Novas publicações aparecerão aqui." />
        @else
            <div class="flex flex-col gap-5">
                @foreach ($publicacoes as $publicacao)
                    <article class="overflow-hidden rounded-xl border border-brand-navy/10 bg-white shadow-sm">
                        <div class="flex items-start gap-3 p-4 pb-3">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-brand-navy text-base font-bold text-white">
                                {{ mb_substr($publicacao->autor->name ?? config('app.name'), 0, 1) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('mural.mostrar', $publicacao) }}" class="font-bold text-brand-ink hover:underline">{{ $publicacao->autor->name ?? 'Administração da Loja' }}</a>
                                <p class="flex items-center gap-1 text-xs text-brand-inkSoft">
                                    {{ $publicacao->publicado_em?->diffForHumans() }}
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0 0c2.21 0 4-4.03 4-9s-1.79-9-4-9-4 4.03-4 9 1.79 9 4 9Zm-8.716-6h17.432M3.284 9h17.432" />
                                    </svg>
                                </p>
                            </div>
                        </div>

                        <div class="px-4 pb-3">
                            <a href="{{ route('mural.mostrar', $publicacao) }}" class="font-bold text-brand-ink hover:underline">{{ $publicacao->titulo }}</a>
                            <p class="mt-1 line-clamp-5 whitespace-pre-line text-brand-ink">{{ $publicacao->conteudo }}</p>
                        </div>

                        @if ($publicacao->imagem_capa)
                            <a href="{{ route('mural.mostrar', $publicacao) }}">
                                <img src="{{ Storage::url($publicacao->imagem_capa) }}" alt="{{ $publicacao->titulo }}" class="aspect-video w-full object-cover">
                            </a>
                        @endif

                        <div class="flex items-center justify-between px-4 py-2.5 text-sm text-brand-inkSoft">
                            <span class="flex items-center gap-1.5">
                                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-brand-navy text-white">
                                    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 10h3v10H2V10Zm5.5 10h9.62a2 2 0 0 0 1.94-1.515l1.34-5.5A2 2 0 0 0 18.46 10H14V6a3 3 0 0 0-3-3l-3 7v10Z" /></svg>
                                </span>
                                {{ $publicacao->reacoes_count }}
                            </span>
                            <a href="{{ route('mural.mostrar', $publicacao) }}" class="hover:underline">{{ $publicacao->comentarios_aprovados_count }} comentário(s)</a>
                        </div>

                        <div class="grid grid-cols-2 border-t border-brand-navy/10">
                            <a href="{{ route('mural.mostrar', $publicacao) }}" class="flex items-center justify-center gap-2 py-2.5 text-sm font-bold text-brand-inkSoft transition hover:bg-brand-paperSoft">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.633 10.5c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 0 1 2.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 0 0 .322-1.672V3a.75.75 0 0 1 .75-.75A2.25 2.25 0 0 1 16.5 4.5c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 0 1-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 0 0-1.423-.23H5.904M14.25 9h2.25M5.904 18.75c.083.205.173.405.27.602.197.4-.078.898-.523.898h-.908c-.889 0-1.713-.518-1.972-1.368a12 12 0 0 1-.521-3.507c0-1.553.295-3.036.831-4.398C3.387 10.203 4.167 9.75 5 9.75h1.053c.472 0 .745.556.5.96a8.958 8.958 0 0 0-1.302 4.665c0 1.194.232 2.333.654 3.375Z" />
                                </svg>
                                Curtir
                            </a>
                            <a href="{{ route('mural.mostrar', $publicacao) }}#comentario-novo" class="flex items-center justify-center gap-2 border-l border-brand-navy/10 py-2.5 text-sm font-bold text-brand-inkSoft transition hover:bg-brand-paperSoft">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                                </svg>
                                Comentar
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-8 border-t border-brand-navy/12 pt-8">{{ $publicacoes->links() }}</div>
        @endif
    </section>
</x-layouts.site>
