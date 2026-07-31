<x-layouts.site titulo="Notícias" meta-descricao="Notícias públicas e comunicados institucionais.">
    <section class="border-b border-brand-navy/10 bg-brand-paperSoft py-10 lg:py-12">
        <div class="mx-auto max-w-6xl px-5 lg:px-8">
            <h1 class="font-siteDisplay text-3xl font-bold text-brand-navy sm:text-4xl">Notícias</h1>
            <p class="mt-2 text-lg text-brand-inkSoft">Acompanhe as publicações e comunicados públicos da Loja.</p>
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-5 py-10 lg:px-8 lg:py-14">
        @if ($noticias->isEmpty())
            <x-ui.empty-state titulo="Nenhuma notícia publicada" descricao="Novas publicações aparecerão aqui." />
        @else
            @php
                $noticiaDestaque = $noticias->currentPage() === 1 ? $noticias->first() : null;
                $noticiasLista = $noticiaDestaque ? $noticias->slice(1) : $noticias;
            @endphp

            @if ($noticiaDestaque)
                <article class="mb-10 border-b border-brand-navy/12 pb-10">
                    <a href="{{ route('noticias.mostrar', $noticiaDestaque->slug) }}" class="mb-5 flex aspect-[16/7] items-center justify-center overflow-hidden rounded-lg bg-gradient-to-br from-brand-navy to-brand-navyDeep">
                        @if ($noticiaDestaque->imagem_capa)
                            <img src="{{ Storage::url($noticiaDestaque->imagem_capa) }}" alt="{{ $noticiaDestaque->titulo }}" class="h-full w-full object-cover">
                        @else
                            <svg class="h-24 w-24 opacity-50" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M100 25 L158 122 H42 Z" stroke="#dcecf2" stroke-width="3" stroke-linejoin="round" />
                                <path d="M52 140 A50 50 0 0 1 148 140" stroke="#dcecf2" stroke-width="3" />
                            </svg>
                        @endif
                    </a>

                    @if ($noticiaDestaque->categoria)
                        <span class="inline-block rounded bg-brand-sky px-3 py-1 text-sm font-bold text-brand-navy">{{ $noticiaDestaque->categoria->nome }}</span>
                    @endif

                    <h2 class="mt-3 font-siteDisplay text-2xl font-bold leading-tight text-brand-navy sm:text-3xl">
                        <a href="{{ route('noticias.mostrar', $noticiaDestaque->slug) }}" class="hover:underline hover:decoration-brand-skyDeep hover:decoration-2 hover:underline-offset-4">{{ $noticiaDestaque->titulo }}</a>
                    </h2>

                    @if ($noticiaDestaque->resumo)
                        <p class="mt-3 max-w-[70ch] text-lg text-brand-inkSoft">{{ $noticiaDestaque->resumo }}</p>
                    @endif

                    <p class="mt-3 text-brand-inkSoft">
                        @if ($noticiaDestaque->publicado_em)
                            Publicado {{ $noticiaDestaque->publicado_em->diffForHumans() }}
                        @endif
                        @if ($noticiaDestaque->autor)
                            · por {{ $noticiaDestaque->autor->name }}
                        @endif
                    </p>
                </article>
            @endif

            @if ($noticiasLista->isNotEmpty())
                <div class="flex flex-col">
                    @foreach ($noticiasLista as $noticia)
                        <a href="{{ route('noticias.mostrar', $noticia->slug) }}" class="flex items-start gap-5 border-t border-brand-navy/12 py-6 first:border-t-0 first:pt-0">
                            <span class="flex aspect-[4/3] w-32 shrink-0 items-center justify-center overflow-hidden rounded-md bg-gradient-to-br from-brand-navy to-brand-navyDeep sm:w-44">
                                @if ($noticia->imagem_capa)
                                    <img src="{{ Storage::url($noticia->imagem_capa) }}" alt="{{ $noticia->titulo }}" class="h-full w-full object-cover">
                                @else
                                    <svg class="h-10 w-10 opacity-50" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path d="M100 25 L158 122 H42 Z" stroke="#dcecf2" stroke-width="3" stroke-linejoin="round" />
                                        <path d="M52 140 A50 50 0 0 1 148 140" stroke="#dcecf2" stroke-width="3" />
                                    </svg>
                                @endif
                            </span>

                            <div class="min-w-0 flex-1">
                                @if ($noticia->categoria)
                                    <span class="text-sm font-bold uppercase tracking-wide text-brand-navy">{{ $noticia->categoria->nome }}</span>
                                @endif

                                <p class="mt-1 font-siteDisplay text-lg font-bold leading-snug text-brand-ink hover:underline sm:text-xl">{{ $noticia->titulo }}</p>

                                @if ($noticia->resumo)
                                    <p class="mt-1.5 hidden max-w-[70ch] text-brand-inkSoft sm:line-clamp-2 sm:block">{{ $noticia->resumo }}</p>
                                @endif

                                @if ($noticia->publicado_em)
                                    <p class="mt-2 text-sm text-brand-inkSoft">{{ $noticia->publicado_em->diffForHumans() }}</p>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif

            <div class="mt-10 border-t border-brand-navy/12 pt-8">{{ $noticias->links() }}</div>
        @endif
    </section>
</x-layouts.site>
