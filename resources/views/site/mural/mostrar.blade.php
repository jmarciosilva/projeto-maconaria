<x-layouts.site :titulo="$publicacao->titulo" meta-descricao="Publicação do Mural da Loja.">
    <section class="mx-auto max-w-2xl px-5 py-10 lg:px-8 lg:py-14">
        <a href="{{ route('mural.index') }}" class="inline-flex items-center gap-1.5 text-sm font-bold text-brand-navy hover:underline">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            Voltar para o mural
        </a>

        <article class="mt-4 overflow-hidden rounded-xl border border-brand-navy/10 bg-white shadow-sm">
            <div class="flex items-start gap-3 p-4 pb-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-brand-navy text-base font-bold text-white">
                    {{ mb_substr($publicacao->autor->name ?? config('app.name'), 0, 1) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="font-bold text-brand-ink">{{ $publicacao->autor->name ?? 'Administração da Loja' }}</p>
                    <p class="flex items-center gap-1 text-xs text-brand-inkSoft">
                        {{ $publicacao->publicado_em?->diffForHumans() }}
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0 0c2.21 0 4-4.03 4-9s-1.79-9-4-9-4 4.03-4 9 1.79 9 4 9Zm-8.716-6h17.432M3.284 9h17.432" />
                        </svg>
                    </p>
                </div>
            </div>

            <div class="px-4 pb-3">
                <h1 class="font-siteDisplay text-lg font-bold text-brand-ink">{{ $publicacao->titulo }}</h1>
                <p class="mt-1 whitespace-pre-line text-brand-ink">{{ $publicacao->conteudo }}</p>
            </div>

            @if ($publicacao->imagem_capa)
                <img src="{{ Storage::url($publicacao->imagem_capa) }}" alt="{{ $publicacao->titulo }}" class="aspect-video w-full object-cover">
            @endif

            <div class="flex items-center justify-between px-4 py-2.5 text-sm text-brand-inkSoft">
                <span class="flex items-center gap-1.5">
                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-brand-navy text-white">
                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 10h3v10H2V10Zm5.5 10h9.62a2 2 0 0 0 1.94-1.515l1.34-5.5A2 2 0 0 0 18.46 10H14V6a3 3 0 0 0-3-3l-3 7v10Z" /></svg>
                    </span>
                    {{ $publicacao->reacoes_count }}
                </span>
                <span>{{ $publicacao->comentarios->count() }} comentário(s)</span>
            </div>

            @auth
                <div class="grid grid-cols-2 border-t border-brand-navy/10">
                    <form method="POST" action="{{ route('mural.reacoes.store', $publicacao) }}" class="contents">
                        @csrf
                        <input type="hidden" name="tipo" value="curtir">
                        <button type="submit" class="flex items-center justify-center gap-2 py-2.5 text-sm font-bold text-brand-inkSoft transition hover:bg-brand-paperSoft">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.633 10.5c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 0 1 2.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 0 0 .322-1.672V3a.75.75 0 0 1 .75-.75A2.25 2.25 0 0 1 16.5 4.5c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 0 1-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 0 0-1.423-.23H5.904M14.25 9h2.25M5.904 18.75c.083.205.173.405.27.602.197.4-.078.898-.523.898h-.908c-.889 0-1.713-.518-1.972-1.368a12 12 0 0 1-.521-3.507c0-1.553.295-3.036.831-4.398C3.387 10.203 4.167 9.75 5 9.75h1.053c.472 0 .745.556.5.96a8.958 8.958 0 0 0-1.302 4.665c0 1.194.232 2.333.654 3.375Z" />
                            </svg>
                            Curtir
                        </button>
                    </form>

                    <a href="#comentario-novo" class="flex items-center justify-center gap-2 border-l border-brand-navy/10 py-2.5 text-sm font-bold text-brand-inkSoft transition hover:bg-brand-paperSoft">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                        </svg>
                        Comentar
                    </a>
                </div>

                <div class="flex items-start gap-3 border-t border-brand-navy/10 p-4" id="comentario-novo">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-navy text-sm font-bold text-white">
                        {{ mb_substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <form method="POST" action="{{ route('mural.comentarios.store', $publicacao) }}" class="flex-1">
                        @csrf
                        <label for="comentario" class="sr-only">Deixe um comentário</label>
                        <textarea id="comentario" name="comentario" rows="2" required maxlength="1000" placeholder="Escreva um comentário..." class="block w-full resize-none rounded-2xl border-brand-navy/15 bg-brand-paperSoft text-sm shadow-none focus:border-brand-navy focus:bg-white focus:ring-brand-navy"></textarea>
                        <div class="mt-2 flex justify-end">
                            <x-ui.button tipo="submit">Comentar</x-ui.button>
                        </div>
                    </form>
                </div>
            @else
                <p class="border-t border-brand-navy/10 p-4 text-sm text-brand-inkSoft">
                    <a href="{{ route('login') }}" class="font-bold text-brand-navy hover:underline">Faça login</a> para comentar ou curtir esta publicação.
                </p>
            @endauth
        </article>

        @if ($publicacao->comentarios->isNotEmpty())
            <div class="mt-6" id="comentarios">
                <h2 class="font-siteDisplay text-lg font-bold text-brand-ink">Comentários</h2>

                <div class="mt-4 flex flex-col gap-3">
                    @foreach ($publicacao->comentarios as $comentario)
                        <div class="flex items-start gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-navy text-sm font-bold text-white">
                                {{ mb_substr($comentario->usuario->name ?? 'U', 0, 1) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="inline-block max-w-full rounded-2xl bg-brand-paperSoft px-3.5 py-2.5">
                                    <p class="text-sm font-bold text-brand-ink">{{ $comentario->usuario->name ?? 'Usuário' }}</p>
                                    <p class="mt-0.5 whitespace-pre-line text-sm text-brand-ink">{{ $comentario->comentario }}</p>
                                </div>
                                <p class="mt-1 pl-3.5 text-xs text-brand-inkSoft">{{ $comentario->created_at?->diffForHumans() }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </section>
</x-layouts.site>
