@props(['titulo' => null, 'metaDescricao' => null])

@php
$configuracaoInstitucional = \App\Models\ConfiguracaoInstitucional::atual();
$logotipoSite = $configuracaoInstitucional->logotipo
    ? asset('storage/'.$configuracaoInstitucional->logotipo)
    : asset('images/logo-loja.png');

$ordemPaginasInstitucionais = [
    'sobre-nos' => 10,
    'maconaria' => 20,
    'maconaria-jovens' => 30,
    'mudar-cidadao' => 40,
    'politica-privacidade' => 90,
    'termos-de-uso' => 100,
];

$paginasInstitucionaisPublicadas = \App\Models\PaginaInstitucional::query()
    ->publicado()
    ->get()
    ->sortBy(fn ($pagina) => sprintf('%03d-%s', $ordemPaginasInstitucionais[$pagina->slug] ?? 50, $pagina->titulo))
    ->values();

$paginasInstitucionaisMenu = $paginasInstitucionaisPublicadas
    ->reject(fn ($pagina) => in_array($pagina->slug, ['politica-privacidade', 'termos-de-uso'], true))
    ->values();

$paginaPoliticaPrivacidade = $paginasInstitucionaisPublicadas->firstWhere('slug', 'politica-privacidade');
$paginaTermosUso = $paginasInstitucionaisPublicadas->firstWhere('slug', 'termos-de-uso');

$urlPaginaInstitucional = function (\App\Models\PaginaInstitucional $pagina): string {
    return match ($pagina->slug) {
        'sobre-nos' => route('paginas.sobre-nos'),
        'maconaria' => route('paginas.maconaria'),
        'maconaria-jovens' => route('paginas.maconaria-jovens'),
        'mudar-cidadao' => route('paginas.mudar-cidadao'),
        'politica-privacidade' => route('paginas.politica-privacidade'),
        'termos-de-uso' => route('paginas.termos-de-uso'),
        default => route('paginas.mostrar', $pagina->slug),
    };
};

// Cada link recebe um destaque visual (sublinhado navy) quando a rota atual
// corresponde a ele — mesma convenção já usada no menu do painel admin.
$linksNav = [
    ['rota' => 'home', 'label' => 'Início', 'ativo' => request()->routeIs('home')],
    ['rota' => 'noticias.index', 'label' => 'Notícias', 'ativo' => request()->routeIs('noticias.*')],
    ['rota' => 'eventos.index', 'label' => 'Eventos', 'ativo' => request()->routeIs('eventos.*') && ! request()->routeIs('calendario')],
    ['rota' => 'calendario', 'label' => 'Calendário', 'ativo' => request()->routeIs('calendario')],
    ['rota' => 'mural.index', 'label' => 'Mural', 'ativo' => request()->routeIs('mural.*')],
    ['rota' => 'galeria.index', 'label' => 'Galeria', 'ativo' => request()->routeIs('galeria.*')],
];
@endphp

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $titulo ? $titulo.' — '.$configuracaoInstitucional->nome() : $configuracaoInstitucional->nome() }}</title>

    @if ($metaDescricao)
        <meta name="description" content="{{ $metaDescricao }}">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white font-siteSans text-[17px] leading-relaxed text-brand-ink antialiased" x-data="{ menuAberto: false }">
    <a href="#conteudo" class="sr-only focus:not-sr-only focus:fixed focus:left-0 focus:top-0 focus:z-50 focus:bg-brand-navy focus:px-4 focus:py-3 focus:font-bold focus:text-white">
        Pular para o conteúdo
    </a>

    <header class="border-b border-brand-navy/10 bg-white">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-5 py-3.5 lg:px-8">
            <a href="{{ route('home') }}" class="flex min-w-0 shrink items-center gap-3">
                <img src="{{ $logotipoSite }}" alt="Selo da {{ $configuracaoInstitucional->nome() }}" class="h-12 w-12 shrink-0 object-contain">
                <span class="font-siteDisplay text-base font-bold leading-tight text-brand-navy sm:text-xl">{{ $configuracaoInstitucional->nome() }}</span>
            </a>

            <nav class="hidden shrink-0 items-center gap-5 text-[0.98rem] font-semibold text-brand-inkSoft xl:flex" aria-label="Navegação principal">
                @foreach ($linksNav as $link)
                    <a
                        href="{{ route($link['rota']) }}"
                        @if ($link['ativo']) aria-current="page" @endif
                        class="border-b-[3px] border-transparent pb-1 hover:text-brand-navy {{ $link['ativo'] ? 'border-brand-navy text-brand-navy' : '' }}"
                    >{{ $link['label'] }}</a>
                @endforeach

                @if ($paginasInstitucionaisMenu->isNotEmpty())
                    <div x-data="{ institucionalAberto: false }" class="relative" @click.outside="institucionalAberto = false">
                        <button type="button" @click="institucionalAberto = !institucionalAberto" class="flex items-center gap-1 hover:text-brand-navy" :aria-expanded="institucionalAberto">
                            Institucional
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                        </button>

                        <div x-show="institucionalAberto" x-cloak class="absolute left-0 z-10 mt-3 w-72 rounded-md border border-brand-navy/10 bg-white py-1 text-brand-ink shadow-lg">
                            @foreach ($paginasInstitucionaisMenu as $paginaMenu)
                                <a href="{{ $urlPaginaInstitucional($paginaMenu) }}" class="block px-4 py-2.5 text-[0.95rem] font-medium hover:bg-brand-paperSoft">{{ $paginaMenu->titulo }}</a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </nav>

            <div class="flex shrink-0 items-center gap-3">
                @auth
                    <a href="{{ route('area-restrita') }}" class="hidden rounded-md bg-brand-navy px-5 py-2.5 text-[0.95rem] font-bold text-white transition hover:bg-brand-navyDeep xl:inline-flex">Área Restrita</a>
                @else
                    <a href="{{ route('login') }}" class="hidden rounded-md bg-brand-navy px-5 py-2.5 text-[0.95rem] font-bold text-white transition hover:bg-brand-navyDeep xl:inline-flex">Entrar</a>
                @endauth

                <button
                    type="button"
                    class="inline-flex h-11 w-11 items-center justify-center rounded-md text-brand-navy xl:hidden"
                    @click="menuAberto = !menuAberto"
                    aria-label="Abrir menu"
                    :aria-expanded="menuAberto"
                >
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                    </svg>
                </button>
            </div>
        </div>

        <nav x-show="menuAberto" x-cloak class="border-t border-brand-navy/10 bg-brand-paperSoft px-5 py-2 xl:hidden" aria-label="Navegação principal (mobile)">
            @foreach ($linksNav as $link)
                <a
                    href="{{ route($link['rota']) }}"
                    @if ($link['ativo']) aria-current="page" @endif
                    class="block border-b border-brand-navy/10 py-3 text-base font-semibold {{ $link['ativo'] ? 'text-brand-navy' : 'text-brand-ink' }}"
                >{{ $link['label'] }}</a>
            @endforeach
            @foreach ($paginasInstitucionaisMenu as $paginaMenu)
                <a href="{{ $urlPaginaInstitucional($paginaMenu) }}" class="block border-b border-brand-navy/10 py-3 text-base font-semibold text-brand-ink">{{ $paginaMenu->titulo }}</a>
            @endforeach
            @auth
                <a href="{{ route('area-restrita') }}" class="block py-3 text-base font-bold text-brand-navy">Área Restrita →</a>
            @else
                <a href="{{ route('login') }}" class="block py-3 text-base font-bold text-brand-navy">Entrar →</a>
            @endauth
        </nav>
    </header>

    <main id="conteudo">
        @if (session('sucesso'))
            <div class="mx-auto mt-4 max-w-6xl px-5 lg:px-8">
                <x-ui.alert tipo="sucesso">{{ session('sucesso') }}</x-ui.alert>
            </div>
        @endif

        @if (session('erro'))
            <div class="mx-auto mt-4 max-w-6xl px-5 lg:px-8">
                <x-ui.alert tipo="erro">{{ session('erro') }}</x-ui.alert>
            </div>
        @endif

        {{ $slot }}
    </main>

    <footer class="mt-16 bg-brand-navy text-white/80">
        <div class="mx-auto max-w-6xl px-5 py-12 lg:px-8">
            <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
                <div class="lg:col-span-1">
                    <p class="font-siteDisplay text-lg font-bold text-white">{{ $configuracaoInstitucional->nome() }}</p>

                    @if ($configuracaoInstitucional->endereco_rodape)
                        <p class="mt-3 max-w-xs whitespace-pre-line text-[0.95rem] leading-relaxed text-white/60">{{ $configuracaoInstitucional->endereco_rodape }}</p>
                    @endif
                </div>

                @if ($paginasInstitucionaisMenu->isNotEmpty())
                    <div>
                        <h2 class="text-[0.95rem] font-bold text-white">Institucional</h2>
                        <ul class="mt-4 flex flex-col gap-2.5 text-[0.95rem]">
                            @foreach ($paginasInstitucionaisMenu as $paginaMenu)
                                <li><a href="{{ $urlPaginaInstitucional($paginaMenu) }}" class="hover:text-white hover:underline">{{ $paginaMenu->titulo }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div>
                    <h2 class="text-[0.95rem] font-bold text-white">Loja</h2>
                    <ul class="mt-4 flex flex-col gap-2.5 text-[0.95rem]">
                        <li><a href="{{ route('noticias.index') }}" class="hover:text-white hover:underline">Notícias</a></li>
                        <li><a href="{{ route('calendario') }}" class="hover:text-white hover:underline">Calendário</a></li>
                        <li><a href="{{ route('galeria.index') }}" class="hover:text-white hover:underline">Galeria</a></li>
                    </ul>
                </div>

                <div>
                    <h2 class="text-[0.95rem] font-bold text-white">Contato</h2>
                    <ul class="mt-4 flex flex-col gap-2.5 text-[0.95rem]">
                        @if ($configuracaoInstitucional->telefone_institucional)
                            <li class="text-white/60">{{ $configuracaoInstitucional->telefone_institucional }}</li>
                        @endif
                        @if ($configuracaoInstitucional->email_institucional)
                            <li><a href="mailto:{{ $configuracaoInstitucional->email_institucional }}" class="hover:text-white hover:underline">{{ $configuracaoInstitucional->email_institucional }}</a></li>
                        @endif
                        @if ($configuracaoInstitucional->possuiRedesSociais())
                            <li class="flex flex-wrap gap-x-4 gap-y-1 pt-1">
                                @if ($configuracaoInstitucional->facebook_url)
                                    <a href="{{ $configuracaoInstitucional->facebook_url }}" target="_blank" rel="noopener noreferrer" class="hover:text-white hover:underline" aria-label="Facebook">Facebook</a>
                                @endif
                                @if ($configuracaoInstitucional->instagram_url)
                                    <a href="{{ $configuracaoInstitucional->instagram_url }}" target="_blank" rel="noopener noreferrer" class="hover:text-white hover:underline" aria-label="Instagram">Instagram</a>
                                @endif
                                @if ($configuracaoInstitucional->twitter_url)
                                    <a href="{{ $configuracaoInstitucional->twitter_url }}" target="_blank" rel="noopener noreferrer" class="hover:text-white hover:underline" aria-label="Twitter / X">Twitter / X</a>
                                @endif
                                @if ($configuracaoInstitucional->tiktok_url)
                                    <a href="{{ $configuracaoInstitucional->tiktok_url }}" target="_blank" rel="noopener noreferrer" class="hover:text-white hover:underline" aria-label="TikTok">TikTok</a>
                                @endif
                            </li>
                        @endif
                    </ul>
                </div>
            </div>

            <div class="mt-10 flex flex-col gap-3 border-t border-white/15 pt-6 text-[0.9rem] text-white/50 sm:flex-row sm:items-center sm:justify-between">
                <p>&copy; {{ now()->year }} {{ $configuracaoInstitucional->nome() }}. Todos os direitos reservados.</p>

                @if ($paginaPoliticaPrivacidade || $paginaTermosUso)
                    <div class="flex gap-5">
                        @if ($paginaPoliticaPrivacidade)
                            <a href="{{ $urlPaginaInstitucional($paginaPoliticaPrivacidade) }}" class="hover:text-white hover:underline">{{ $paginaPoliticaPrivacidade->titulo }}</a>
                        @endif

                        @if ($paginaTermosUso)
                            <a href="{{ $urlPaginaInstitucional($paginaTermosUso) }}" class="hover:text-white hover:underline">{{ $paginaTermosUso->titulo }}</a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </footer>
</body>
</html>
