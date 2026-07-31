<x-layouts.site titulo="Eventos" meta-descricao="Eventos públicos da Loja.">
    <section class="border-b border-brand-navy/10 bg-brand-paperSoft py-10 lg:py-12">
        <div class="mx-auto max-w-6xl px-5 lg:px-8">
            <h1 class="font-siteDisplay text-3xl font-bold text-brand-navy sm:text-4xl">Eventos</h1>
            <p class="mt-2 text-lg text-brand-inkSoft">Agenda pública de eventos institucionais.</p>
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-5 py-10 lg:px-8 lg:py-14">
        @if ($eventos->isEmpty())
            <x-ui.empty-state titulo="Nenhum evento público previsto" descricao="Novos eventos aparecerão aqui." />
        @else
            @php
                $eventoDestaque = $eventos->currentPage() === 1 ? $eventos->first() : null;
                $eventosLista = $eventoDestaque ? $eventos->slice(1) : $eventos;
            @endphp

            @if ($eventoDestaque)
                <article class="mb-10 border-b border-brand-navy/12 pb-10">
                    <a href="{{ route('eventos.mostrar', $eventoDestaque->slug) }}" class="mb-5 flex aspect-[16/7] items-center justify-center overflow-hidden rounded-lg bg-gradient-to-br from-brand-navy to-brand-navyDeep">
                        @if ($eventoDestaque->imagem_capa)
                            <img src="{{ Storage::url($eventoDestaque->imagem_capa) }}" alt="{{ $eventoDestaque->titulo }}" class="h-full w-full object-contain">
                        @else
                            <svg class="h-24 w-24 opacity-50" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M100 25 L158 122 H42 Z" stroke="#dcecf2" stroke-width="3" stroke-linejoin="round" />
                                <path d="M52 140 A50 50 0 0 1 148 140" stroke="#dcecf2" stroke-width="3" />
                            </svg>
                        @endif
                    </a>

                    <span class="inline-block rounded bg-brand-sky px-3 py-1 text-sm font-bold text-brand-navy">{{ $eventoDestaque->tipo->rotulo() }}</span>

                    <h2 class="mt-3 font-siteDisplay text-2xl font-bold leading-tight text-brand-navy sm:text-3xl">
                        <a href="{{ route('eventos.mostrar', $eventoDestaque->slug) }}" class="hover:underline hover:decoration-brand-skyDeep hover:decoration-2 hover:underline-offset-4">{{ $eventoDestaque->titulo }}</a>
                    </h2>

                    @if ($eventoDestaque->descricao)
                        <p class="mt-3 max-w-[70ch] text-lg text-brand-inkSoft">{{ $eventoDestaque->descricao }}</p>
                    @endif

                    <p class="mt-3 text-brand-inkSoft">
                        {{ $eventoDestaque->inicio_em->format('d/m/Y \à\s H:i') }}
                        @if ($eventoDestaque->local)
                            · {{ $eventoDestaque->local }}
                        @endif
                    </p>
                </article>
            @endif

            @if ($eventosLista->isNotEmpty())
                <div class="flex flex-col">
                    @foreach ($eventosLista as $evento)
                        <a href="{{ route('eventos.mostrar', $evento->slug) }}" class="flex items-start gap-5 border-t border-brand-navy/12 py-6 first:border-t-0 first:pt-0">
                            <span class="flex aspect-[4/3] w-32 shrink-0 items-center justify-center overflow-hidden rounded-md bg-gradient-to-br from-brand-navy to-brand-navyDeep sm:w-44">
                                @if ($evento->imagem_capa)
                                    <img src="{{ Storage::url($evento->imagem_capa) }}" alt="{{ $evento->titulo }}" class="h-full w-full object-contain">
                                @else
                                    <svg class="h-10 w-10 opacity-50" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path d="M100 25 L158 122 H42 Z" stroke="#dcecf2" stroke-width="3" stroke-linejoin="round" />
                                        <path d="M52 140 A50 50 0 0 1 148 140" stroke="#dcecf2" stroke-width="3" />
                                    </svg>
                                @endif
                            </span>

                            <div class="min-w-0 flex-1">
                                <span class="text-sm font-bold uppercase tracking-wide text-brand-navy">{{ $evento->tipo->rotulo() }}</span>

                                <p class="mt-1 font-siteDisplay text-lg font-bold leading-snug text-brand-ink hover:underline sm:text-xl">{{ $evento->titulo }}</p>

                                @if ($evento->descricao)
                                    <p class="mt-1.5 hidden max-w-[70ch] text-brand-inkSoft sm:line-clamp-2 sm:block">{{ $evento->descricao }}</p>
                                @endif

                                <p class="mt-2 text-sm text-brand-inkSoft">
                                    {{ $evento->inicio_em->format('d/m/Y \à\s H:i') }}
                                    @if ($evento->local)
                                        · {{ $evento->local }}
                                    @endif
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif

            <div class="mt-10 border-t border-brand-navy/12 pt-8">{{ $eventos->links() }}</div>
        @endif
    </section>
</x-layouts.site>
