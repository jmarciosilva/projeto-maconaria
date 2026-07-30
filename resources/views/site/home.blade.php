@php
$configuracaoInstitucional = \App\Models\ConfiguracaoInstitucional::atual();
@endphp

<x-layouts.site :meta-descricao="$configuracaoInstitucional->subtitulo_institucional">
    @if ($itensCarrossel->isEmpty())
        <section class="bg-gradient-to-b from-blue-950 to-blue-900 py-20 text-white">
            <div class="mx-auto max-w-5xl px-4 text-center sm:px-6 lg:px-8">
                <h1 class="text-3xl font-bold sm:text-4xl">{{ $configuracaoInstitucional->titulo_institucional ?: $configuracaoInstitucional->nome() }}</h1>
                <p class="mx-auto mt-4 max-w-2xl text-blue-100">
                    {{ $configuracaoInstitucional->subtitulo_institucional ?: 'Augusta e Respeitável Loja Simbólica Ferraz de Vasconcelos nº 2516 — Benfeitora da Ordem.' }}
                </p>
            </div>
        </section>
    @else
        <section
            x-data="{ indice: 0, total: {{ $itensCarrossel->count() }} }"
            x-init="setInterval(() => indice = (indice + 1) % total, 6000)"
            class="relative overflow-hidden bg-blue-950"
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
                            <div class="absolute inset-0 flex items-end bg-gradient-to-t from-black/70 via-black/20 to-transparent">
                                <div class="mx-auto w-full max-w-5xl px-4 pb-10 text-white sm:px-6 lg:px-8">
                                    @if ($item->titulo)
                                        <h2 class="text-2xl font-bold sm:text-3xl">{{ $item->titulo }}</h2>
                                    @endif

                                    @if ($item->subtitulo)
                                        <p class="mt-2 max-w-2xl text-blue-100">{{ $item->subtitulo }}</p>
                                    @endif

                                    @if ($item->link && $item->texto_botao)
                                        <a
                                            href="{{ $item->link }}"
                                            @if ($item->abrir_em_nova_aba) target="_blank" rel="noopener noreferrer" @endif
                                            class="mt-4 inline-block rounded-md bg-[#C9A227] px-5 py-2 text-sm font-semibold text-[#14213D] hover:bg-[#b8931f]"
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
                    class="absolute left-2 top-1/2 -translate-y-1/2 rounded-full bg-black/40 p-2 text-white hover:bg-black/60"
                    aria-label="Slide anterior"
                >
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
                </button>

                <button
                    type="button"
                    @click="indice = (indice + 1) % total"
                    class="absolute right-2 top-1/2 -translate-y-1/2 rounded-full bg-black/40 p-2 text-white hover:bg-black/60"
                    aria-label="Próximo slide"
                >
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                </button>

                <div class="absolute bottom-3 left-1/2 flex -translate-x-1/2 gap-2">
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
        <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="mb-8 max-w-3xl">
                <h2 class="text-2xl font-bold text-gray-900">Conheça a Loja e a Maçonaria</h2>
                <p class="mt-2 text-gray-600">Acesse os conteúdos institucionais preparados pela administração da Loja.</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($paginasInstitucionais as $pagina)
                    <a href="{{ route('paginas.mostrar', $pagina->slug) }}" class="rounded-md border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md">
                        <h3 class="text-base font-semibold text-gray-900">{{ $pagina->titulo }}</h3>

                        @if ($pagina->meta_descricao)
                            <p class="mt-2 line-clamp-3 text-sm text-gray-600">{{ $pagina->meta_descricao }}</p>
                        @endif

                        <span class="mt-4 inline-flex text-sm font-semibold text-blue-800">Ler conteúdo</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @if ($noticiasEmDestaque->isNotEmpty())
        <section class="bg-gray-50 py-16">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Notícias em destaque</h2>
                        <p class="mt-2 text-gray-600">Comunicados e conteúdos públicos recentes.</p>
                    </div>

                    <a href="{{ route('noticias.index') }}" class="text-sm font-semibold text-blue-800 hover:underline">Ver todas</a>
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    @foreach ($noticiasEmDestaque as $noticia)
                        <a href="{{ route('noticias.mostrar', $noticia->slug) }}" class="rounded-md border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md">
                            @if ($noticia->categoria)
                                <p class="text-xs font-semibold uppercase tracking-wide text-blue-800">{{ $noticia->categoria->nome }}</p>
                            @endif

                            <h3 class="mt-2 text-lg font-semibold text-gray-900">{{ $noticia->titulo }}</h3>

                            @if ($noticia->resumo)
                                <p class="mt-2 line-clamp-3 text-sm text-gray-600">{{ $noticia->resumo }}</p>
                            @endif

                            <span class="mt-4 inline-flex text-sm font-semibold text-blue-800">Ler notícia</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($proximosEventos->isNotEmpty())
        <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Próximos eventos</h2>
                    <p class="mt-2 text-gray-600">Agenda pública da Loja.</p>
                </div>

                <a href="{{ route('eventos.index') }}" class="text-sm font-semibold text-blue-800 hover:underline">Ver agenda</a>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                @foreach ($proximosEventos as $evento)
                    <a href="{{ route('eventos.mostrar', $evento->slug) }}" class="rounded-md border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md">
                        <p class="text-xs font-semibold uppercase tracking-wide text-blue-800">{{ $evento->tipo->rotulo() }}</p>
                        <h3 class="mt-2 text-lg font-semibold text-gray-900">{{ $evento->titulo }}</h3>
                        <p class="mt-2 text-sm text-gray-600">{{ $evento->inicio_em->format('d/m/Y H:i') }}</p>

                        @if ($evento->local)
                            <p class="mt-1 text-sm text-gray-600">{{ $evento->local }}</p>
                        @endif
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @if ($publicacoesMural->isNotEmpty())
        <section class="bg-gray-50 py-16">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="mb-8 max-w-3xl">
                    <h2 class="text-2xl font-bold text-gray-900">Mural da Loja</h2>
                    <p class="mt-2 text-gray-600">Publicações públicas da Loja, com comentários moderados e reações da comunidade.</p>
                </div>

                <div class="grid gap-5 lg:grid-cols-3">
                    @foreach ($publicacoesMural as $publicacao)
                        <article class="rounded-md border border-gray-200 bg-white p-5 shadow-sm">
                            <div class="mb-4 flex items-start gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-950 text-sm font-semibold text-white">
                                    {{ mb_substr($publicacao->autor->name ?? config('app.name'), 0, 1) }}
                                </div>
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900">{{ $publicacao->titulo }}</h3>
                                    <p class="text-xs text-gray-500">{{ $publicacao->autor->name ?? 'Administração da Loja' }} · {{ $publicacao->publicado_em?->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>

                            <p class="whitespace-pre-line text-sm text-gray-700">{{ $publicacao->conteudo }}</p>

                            <div class="mt-4 flex items-center justify-between border-y border-gray-100 py-3 text-sm text-gray-600">
                                <span>{{ $publicacao->reacoes_count }} curtida(s)</span>
                                <span>{{ $publicacao->comentarios_aprovados_count }} comentário(s)</span>
                            </div>

                            @auth
                                <form method="POST" action="{{ route('mural.reacoes.store', $publicacao) }}" class="mt-3">
                                    @csrf
                                    <input type="hidden" name="tipo" value="curtir">
                                    <button type="submit" class="w-full rounded-md border border-blue-200 px-3 py-2 text-sm font-semibold text-blue-800 hover:bg-blue-50">Curtir</button>
                                </form>
                            @else
                                <a href="{{ route('login') }}" class="mt-3 block rounded-md border border-blue-200 px-3 py-2 text-center text-sm font-semibold text-blue-800 hover:bg-blue-50">Entrar para curtir</a>
                            @endauth

                            @if ($publicacao->comentarios->isNotEmpty())
                                <div class="mt-4 space-y-3">
                                    @foreach ($publicacao->comentarios->take(2) as $comentario)
                                        <div class="rounded-md bg-gray-50 p-3 text-sm">
                                            <p class="font-medium text-gray-900">{{ $comentario->usuario->name ?? 'Usuário' }}</p>
                                            <p class="mt-1 text-gray-700">{{ $comentario->comentario }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @auth
                                <form method="POST" action="{{ route('mural.comentarios.store', $publicacao) }}" class="mt-4 space-y-2">
                                    @csrf
                                    <textarea name="comentario" rows="2" class="block w-full rounded-md border-gray-300 text-sm shadow-sm" placeholder="Escreva um comentário" required></textarea>
                                    <button type="submit" class="text-sm font-semibold text-blue-800 hover:underline">Comentar</button>
                                </form>
                            @endauth
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($albunsGaleria->isNotEmpty())
        <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="mb-8 max-w-3xl">
                <h2 class="text-2xl font-bold text-gray-900">Galeria da Loja</h2>
                <p class="mt-2 text-gray-600">Registros públicos de eventos, sessões e momentos institucionais.</p>
            </div>

            <div class="grid gap-5 md:grid-cols-3">
                @foreach ($albunsGaleria as $album)
                    @php($fotoPrincipal = $album->fotografias->first())
                    <article class="overflow-hidden rounded-md border border-gray-200 bg-white shadow-sm">
                        @if ($fotoPrincipal)
                            <img src="{{ Storage::url($fotoPrincipal->caminho) }}" alt="{{ $fotoPrincipal->texto_alternativo }}" class="aspect-video w-full object-cover">
                        @else
                            <div class="flex aspect-video w-full items-center justify-center bg-blue-950 text-sm font-semibold text-white">Galeria</div>
                        @endif
                        <div class="p-5">
                            <h3 class="text-base font-semibold text-gray-900">{{ $album->titulo }}</h3>
                            @if ($album->descricao)
                                <p class="mt-2 line-clamp-3 text-sm text-gray-600">{{ $album->descricao }}</p>
                            @endif
                            <p class="mt-3 text-sm text-gray-500">{{ $album->fotografias_count }} fotografia(s)</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
</x-layouts.site>
