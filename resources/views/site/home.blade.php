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
</x-layouts.site>
