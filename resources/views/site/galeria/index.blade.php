<x-layouts.site titulo="Galeria da Loja" meta-descricao="Registros públicos de eventos, sessões e momentos institucionais da Loja.">
    <section class="border-b border-brand-navy/10 bg-brand-paperSoft py-10 lg:py-12">
        <div class="mx-auto max-w-6xl px-5 lg:px-8">
            <h1 class="font-siteDisplay text-3xl font-bold text-brand-navy sm:text-4xl">Galeria da Loja</h1>
            <p class="mt-2 text-lg text-brand-inkSoft">Registros públicos de eventos, sessões e momentos institucionais.</p>
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-5 py-10 lg:px-8 lg:py-14">
        @if ($albuns->isEmpty())
            <x-ui.empty-state titulo="Nenhum álbum público no momento" descricao="Novos álbuns aparecerão aqui." />
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($albuns as $album)
                    @php($fotoPrincipal = $album->fotografias->first())
                    <a href="{{ route('galeria.mostrar', $album->slug) }}" class="group overflow-hidden rounded-xl border border-brand-navy/10 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                        <div class="relative">
                            @if ($fotoPrincipal)
                                <img src="{{ Storage::url($fotoPrincipal->caminho) }}" alt="{{ $fotoPrincipal->texto_alternativo }}" class="aspect-video w-full object-cover">
                            @else
                                <div class="flex aspect-video w-full items-center justify-center bg-gradient-to-br from-brand-navy to-brand-navyDeep">
                                    <svg class="h-12 w-12 opacity-50" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path d="M100 25 L158 122 H42 Z" stroke="#dcecf2" stroke-width="3" stroke-linejoin="round" />
                                        <path d="M52 140 A50 50 0 0 1 148 140" stroke="#dcecf2" stroke-width="3" />
                                    </svg>
                                </div>
                            @endif

                            <span class="absolute bottom-3 right-3 flex items-center gap-1.5 rounded-full bg-black/60 px-2.5 py-1 text-xs font-bold text-white">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3 12V4.5A1.5 1.5 0 0 1 4.5 3h15A1.5 1.5 0 0 1 21 4.5v15a1.5 1.5 0 0 1-1.5 1.5H4.5A1.5 1.5 0 0 1 3 19.5v-1.875M9 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
                                </svg>
                                {{ $album->fotografias_count }}
                            </span>
                        </div>

                        <div class="p-4">
                            <div class="flex items-center gap-2 text-xs text-brand-inkSoft">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand-navy text-[0.65rem] font-bold text-white">
                                    {{ mb_substr($album->autor->name ?? config('app.name'), 0, 1) }}
                                </span>
                                <span class="truncate">{{ $album->autor->name ?? 'Administração da Loja' }} · {{ $album->publicado_em?->diffForHumans() }}</span>
                            </div>

                            <h2 class="mt-2 font-siteDisplay text-lg font-bold text-brand-navy group-hover:underline">{{ $album->titulo }}</h2>

                            @if ($album->descricao)
                                <p class="mt-1 line-clamp-2 text-sm text-brand-inkSoft">{{ $album->descricao }}</p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-10 border-t border-brand-navy/12 pt-8">{{ $albuns->links() }}</div>
        @endif
    </section>
</x-layouts.site>
