@php
$configuracaoInstitucional = \App\Models\ConfiguracaoInstitucional::atual();
$noticiaPrincipal = $noticiasEmDestaque->first();
$noticiasSecundarias = $noticiasEmDestaque->slice(1);
@endphp

<x-layouts.site :meta-descricao="$configuracaoInstitucional->subtitulo_institucional">
    @if ($itensCarrossel->isEmpty())
        <section class="bg-gradient-to-b from-brand-navy to-brand-navyDeep py-20 text-white">
            <div class="mx-auto max-w-5xl px-5 text-center lg:px-8">
                <h1 class="font-siteDisplay text-3xl font-bold sm:text-4xl">{{ $configuracaoInstitucional->titulo_institucional ?: $configuracaoInstitucional->nome() }}</h1>
                <p class="mx-auto mt-4 max-w-2xl text-lg text-white/85">
                    {{ $configuracaoInstitucional->subtitulo_institucional ?: 'Augusta e Respeitável Loja Simbólica Ferraz de Vasconcelos nº 2516 — Benfeitora da Ordem.' }}
                </p>
            </div>
        </section>
    @else
        <section
            x-data="{ indice: 0, total: {{ $itensCarrossel->count() }} }"
            x-init="setInterval(() => indice = (indice + 1) % total, 6000)"
            class="relative overflow-hidden bg-brand-navyDeep"
            role="region"
            aria-label="Carrossel de destaques"
        >
            <div class="relative h-[420px] sm:h-[480px]">
                @foreach ($itensCarrossel as $posicao => $item)
                    <div
                        x-show="indice === {{ $posicao }}"
                        x-transition:enter="transition ease-out duration-500"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        class="absolute inset-0"
                    >
                        <img
                            src="{{ asset('storage/'.$item->imagem_desktop) }}"
                            alt="{{ $item->texto_alternativo }}"
                            class="hidden h-full w-full object-cover sm:block"
                        >
                        <img
                            src="{{ asset('storage/'.($item->imagem_mobile ?? $item->imagem_desktop)) }}"
                            alt="{{ $item->texto_alternativo }}"
                            class="block h-full w-full object-cover sm:hidden"
                        >

                        @if ($item->titulo || $item->subtitulo || $item->link)
                            <div class="absolute inset-0 flex items-end bg-gradient-to-t from-brand-navyDeep/90 via-brand-navyDeep/30 to-transparent">
                                <div class="mx-auto w-full max-w-6xl px-5 pb-11 text-white lg:px-8">
                                    @if ($item->titulo)
                                        <h2 class="font-siteDisplay text-2xl font-bold leading-tight sm:text-3xl">{{ $item->titulo }}</h2>
                                    @endif

                                    @if ($item->subtitulo)
                                        <p class="mt-2.5 max-w-2xl text-lg text-white/85">{{ $item->subtitulo }}</p>
                                    @endif

                                    @if ($item->link && $item->texto_botao)
                                        <a
                                            href="{{ $item->link }}"
                                            @if ($item->abrir_em_nova_aba) target="_blank" rel="noopener noreferrer" @endif
                                            class="mt-5 inline-block rounded-md bg-white px-6 py-2.5 font-bold text-brand-navy hover:bg-brand-sky"
                                        >
                                            {{ $item->texto_botao }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            @if ($itensCarrossel->count() > 1)
                <button
                    type="button"
                    @click="indice = (indice - 1 + total) % total"
                    class="absolute left-3 top-1/2 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-brand-navyDeep/50 text-white hover:bg-brand-navyDeep/75"
                    aria-label="Slide anterior"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
                </button>

                <button
                    type="button"
                    @click="indice = (indice + 1) % total"
                    class="absolute right-3 top-1/2 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-brand-navyDeep/50 text-white hover:bg-brand-navyDeep/75"
                    aria-label="Próximo slide"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                </button>

                <div class="absolute bottom-4 left-1/2 flex -translate-x-1/2 gap-2.5">
                    @foreach ($itensCarrossel as $posicao => $item)
                        <button
                            type="button"
                            @click="indice = {{ $posicao }}"
                            class="h-2.5 w-2.5 rounded-full"
                            :class="indice === {{ $posicao }} ? 'bg-white' : 'bg-white/40'"
                            aria-label="Ir para o slide {{ $posicao + 1 }}"
                        ></button>
                    @endforeach
                </div>
            @endif
        </section>
    @endif

    @if ($paginasInstitucionais->isNotEmpty())
        <section class="bg-brand-paperSoft py-14 lg:py-16">
            <div class="mx-auto max-w-6xl px-5 lg:px-8">
                <div class="mb-7 max-w-2xl">
                    <h2 class="font-siteDisplay text-2xl font-bold text-brand-navy sm:text-3xl">Conheça a Loja e a Maçonaria</h2>
                    <p class="mt-1 text-brand-inkSoft">Acesse os conteúdos institucionais preparados pela administração da Loja.</p>
                </div>

                <div class="flex flex-wrap gap-4">
                    @foreach ($paginasInstitucionais as $pagina)
                        <a
                            href="{{ $pagina->urlPublica() }}"
                            class="rounded-full border-2 border-brand-navy bg-white px-6 py-3 font-bold text-brand-navy transition hover:bg-brand-navy hover:text-white"
                        >{{ $pagina->titulo }}</a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($noticiasEmDestaque->isNotEmpty())
        <section class="py-14 lg:py-16">
            <div class="mx-auto max-w-6xl px-5 lg:px-8">
                <div class="mb-7 flex flex-wrap items-end justify-between gap-4 border-b border-brand-navy/15 pb-4">
                    <div>
                        <h2 class="font-siteDisplay text-2xl font-bold text-brand-navy sm:text-3xl">Notícias</h2>
                        <p class="mt-1 text-brand-inkSoft">Comunicados e conteúdos públicos recentes.</p>
                    </div>

                    <a href="{{ route('noticias.index') }}" class="border-b-2 border-brand-skyDeep font-bold text-brand-navy hover:border-brand-navy">Todas as notícias →</a>
                </div>

                <div class="grid gap-10 {{ $noticiasSecundarias->isNotEmpty() ? 'lg:grid-cols-[1.7fr_1fr]' : '' }}">
                    <article>
                        <a href="{{ route('noticias.mostrar', $noticiaPrincipal->slug) }}" class="mb-5 flex aspect-[16/10] items-center justify-center overflow-hidden rounded-lg bg-gradient-to-br from-brand-navy to-brand-navyDeep">
                            @if ($noticiaPrincipal->imagem_capa)
                                <img src="{{ Storage::url($noticiaPrincipal->imagem_capa) }}" alt="{{ $noticiaPrincipal->titulo }}" class="h-full w-full object-cover">
                            @else
                                <svg class="h-24 w-24 opacity-50" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path d="M100 25 L158 122 H42 Z" stroke="#dcecf2" stroke-width="3" stroke-linejoin="round" />
                                    <path d="M52 140 A50 50 0 0 1 148 140" stroke="#dcecf2" stroke-width="3" />
                                </svg>
                            @endif
                        </a>

                        @if ($noticiaPrincipal->categoria)
                            <span class="inline-block rounded bg-brand-sky px-3 py-1 text-sm font-bold text-brand-navy">{{ $noticiaPrincipal->categoria->nome }}</span>
                        @endif

                        <h3 class="mt-3 font-siteDisplay text-2xl font-bold leading-tight text-brand-navy sm:text-3xl">
                            <a href="{{ route('noticias.mostrar', $noticiaPrincipal->slug) }}" class="hover:underline hover:decoration-brand-skyDeep hover:decoration-2 hover:underline-offset-4">{{ $noticiaPrincipal->titulo }}</a>
                        </h3>

                        @if ($noticiaPrincipal->resumo)
                            <p class="mt-3 max-w-[60ch] text-lg text-brand-inkSoft">{{ $noticiaPrincipal->resumo }}</p>
                        @endif

                        <p class="mt-3 text-brand-inkSoft">
                            @if ($noticiaPrincipal->publicado_em)
                                Publicado {{ $noticiaPrincipal->publicado_em->diffForHumans() }}
                            @endif
                            @if ($noticiaPrincipal->autor)
                                · por {{ $noticiaPrincipal->autor->name }}
                            @endif
                        </p>
                    </article>

                    @if ($noticiasSecundarias->isNotEmpty())
                        <aside class="flex flex-col" aria-label="Mais notícias">
                            @foreach ($noticiasSecundarias as $noticia)
                                <a href="{{ route('noticias.mostrar', $noticia->slug) }}" class="flex items-start gap-4 border-t border-brand-navy/12 py-5 first:border-t-0 first:pt-0">
                                    @if ($noticia->imagem_capa)
                                        <img src="{{ Storage::url($noticia->imagem_capa) }}" alt="{{ $noticia->titulo }}" class="aspect-[4/3] w-24 shrink-0 rounded-md object-cover">
                                    @endif

                                    <div class="min-w-0 flex-1">
                                        @if ($noticia->categoria)
                                            <span class="text-sm font-bold uppercase tracking-wide text-brand-navy">{{ $noticia->categoria->nome }}</span>
                                        @endif
                                        <p class="mt-2 font-bold leading-snug text-brand-ink hover:underline">{{ $noticia->titulo }}</p>
                                        @if ($noticia->publicado_em)
                                            <p class="mt-2 text-sm text-brand-inkSoft">{{ $noticia->publicado_em->diffForHumans() }}</p>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </aside>
                    @endif
                </div>
            </div>
        </section>
    @endif

    @if ($proximosEventos->isNotEmpty())
        <section class="bg-brand-paperSoft py-14 lg:py-16">
            <div class="mx-auto max-w-6xl px-5 lg:px-8">
                <div class="mb-7 flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <h2 class="font-siteDisplay text-2xl font-bold text-brand-navy sm:text-3xl">Próximos encontros</h2>
                        <p class="mt-1 text-brand-inkSoft">Agenda pública de sessões e atividades.</p>
                    </div>

                    <a href="{{ route('calendario') }}" class="border-b-2 border-brand-skyDeep font-bold text-brand-navy hover:border-brand-navy">Ver calendário completo →</a>
                </div>

                <div class="grid gap-5 md:grid-cols-3">
                    @foreach ($proximosEventos as $evento)
                        <a href="{{ route('eventos.mostrar', $evento->slug) }}" class="flex overflow-hidden rounded-lg border border-brand-navy/10 bg-white shadow-sm transition hover:shadow-md">
                            <div class="flex w-20 shrink-0 flex-col items-center justify-center bg-brand-navy py-4 text-white">
                                <span class="font-siteDisplay text-3xl font-bold leading-none">{{ $evento->inicio_em->format('d') }}</span>
                                <span class="mt-1 text-xs font-bold uppercase tracking-wide text-brand-sky">{{ Illuminate\Support\Str::upper($evento->inicio_em->translatedFormat('M')) }}</span>
                            </div>

                            @if ($evento->imagem_capa)
                                <img src="{{ Storage::url($evento->imagem_capa) }}" alt="{{ $evento->titulo }}" class="w-20 shrink-0 object-cover">
                            @endif

                            <div class="flex flex-col justify-center px-4 py-3">
                                <p class="font-bold leading-snug text-brand-ink">{{ $evento->titulo }}</p>
                                <p class="mt-1.5 text-sm text-brand-inkSoft">
                                    {{ $evento->inicio_em->format('H:i') }}
                                    @if ($evento->local)
                                        · {{ $evento->local }}
                                    @endif
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($publicacoesMural->isNotEmpty())
        <section class="py-14 lg:py-16">
            <div class="mx-auto max-w-6xl px-5 lg:px-8">
                <div class="mb-7 flex flex-wrap items-end justify-between gap-4">
                    <div class="max-w-2xl">
                        <h2 class="font-siteDisplay text-2xl font-bold text-brand-navy sm:text-3xl">Mural da Loja</h2>
                        <p class="mt-1 text-brand-inkSoft">Publicações públicas da Loja, com comentários moderados e reações da comunidade.</p>
                    </div>
                    <a href="{{ route('mural.index') }}" class="border-b-2 border-brand-skyDeep font-bold text-brand-navy hover:border-brand-navy">Ver mural completo →</a>
                </div>

                <div class="grid gap-6 lg:grid-cols-3">
                    @foreach ($publicacoesMural as $publicacao)
                        <article class="flex flex-col overflow-hidden rounded-lg border border-brand-navy/10 bg-white shadow-sm">
                            @if ($publicacao->imagem_capa)
                                <img src="{{ Storage::url($publicacao->imagem_capa) }}" alt="{{ $publicacao->titulo }}" class="aspect-video w-full object-cover">
                            @endif

                            <div class="flex flex-col gap-3.5 p-6">
                            <div class="flex items-center gap-3">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-brand-navy font-siteDisplay text-lg font-bold text-white">
                                    {{ mb_substr($publicacao->autor->name ?? config('app.name'), 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-siteDisplay font-bold text-brand-navy">{{ $publicacao->titulo }}</p>
                                    <p class="text-sm text-brand-inkSoft">{{ $publicacao->autor->name ?? 'Administração da Loja' }} · {{ $publicacao->publicado_em?->diffForHumans() }}</p>
                                </div>
                            </div>

                            <p class="whitespace-pre-line text-brand-ink">{{ $publicacao->conteudo }}</p>

                            <div class="flex items-center justify-between border-t border-brand-navy/10 pt-3 text-sm text-brand-inkSoft">
                                <span>♥ {{ $publicacao->reacoes_count }} curtida(s)</span>
                                <span>{{ $publicacao->comentarios_aprovados_count }} comentário(s)</span>
                            </div>

                            @if ($publicacao->comentarios->isNotEmpty())
                                <div class="flex flex-col gap-2.5">
                                    @foreach ($publicacao->comentarios->take(2) as $comentario)
                                        <div class="rounded-md bg-brand-paperSoft p-3 text-sm">
                                            <p class="font-bold text-brand-ink">{{ $comentario->usuario->name ?? 'Usuário' }}</p>
                                            <p class="mt-1 text-brand-inkSoft">{{ $comentario->comentario }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @auth
                                <form method="POST" action="{{ route('mural.reacoes.store', $publicacao) }}">
                                    @csrf
                                    <input type="hidden" name="tipo" value="curtir">
                                    <button type="submit" class="w-full rounded-md border-2 border-brand-skyDeep py-2.5 font-bold text-brand-navy hover:bg-brand-sky">Curtir</button>
                                </form>

                                <form method="POST" action="{{ route('mural.comentarios.store', $publicacao) }}" class="flex flex-col gap-2">
                                    @csrf
                                    <textarea name="comentario" rows="2" class="block w-full rounded-md border-brand-navy/20 text-sm shadow-sm focus:border-brand-navy focus:ring-brand-navy" placeholder="Escreva um comentário" required></textarea>
                                    <button type="submit" class="self-start font-bold text-brand-navy hover:underline">Comentar</button>
                                </form>
                            @else
                                <a href="{{ route('login') }}" class="block rounded-md border-2 border-brand-skyDeep py-2.5 text-center font-bold text-brand-navy hover:bg-brand-sky">Entrar para curtir</a>
                            @endauth
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($albunsGaleria->isNotEmpty())
        <section class="bg-brand-paperSoft py-14 lg:py-16">
            <div class="mx-auto max-w-6xl px-5 lg:px-8">
                <div class="mb-7 flex flex-wrap items-end justify-between gap-4">
                    <div class="max-w-2xl">
                        <h2 class="font-siteDisplay text-2xl font-bold text-brand-navy sm:text-3xl">Galeria da Loja</h2>
                        <p class="mt-1 text-brand-inkSoft">Registros públicos de eventos, sessões e momentos institucionais.</p>
                    </div>
                    <a href="{{ route('galeria.index') }}" class="border-b-2 border-brand-skyDeep font-bold text-brand-navy hover:border-brand-navy">Ver galeria completa →</a>
                </div>

                <div class="grid gap-6 md:grid-cols-3">
                    @foreach ($albunsGaleria as $album)
                        @php($fotoPrincipal = $album->fotografias->first())
                        <a href="{{ route('galeria.mostrar', $album->slug) }}" class="overflow-hidden rounded-lg border border-brand-navy/10 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                            @if ($fotoPrincipal)
                                <img src="{{ Storage::url($fotoPrincipal->caminho) }}" alt="{{ $fotoPrincipal->texto_alternativo }}" class="aspect-[4/3] w-full object-cover">
                            @else
                                <div class="flex aspect-[4/3] w-full items-center justify-center bg-gradient-to-br from-brand-skyDeep to-brand-navy">
                                    <svg class="h-14 w-14 opacity-70" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path d="M50 15 L78 62 H22 Z" stroke="#fff" stroke-width="2" />
                                    </svg>
                                </div>
                            @endif
                            <div class="p-5">
                                <h3 class="font-bold text-brand-ink">{{ $album->titulo }}</h3>
                                @if ($album->descricao)
                                    <p class="mt-1.5 line-clamp-2 text-sm text-brand-inkSoft">{{ $album->descricao }}</p>
                                @endif
                                <p class="mt-3 text-sm text-brand-inkSoft">{{ $album->fotografias_count }} fotografia(s)</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts.site>
