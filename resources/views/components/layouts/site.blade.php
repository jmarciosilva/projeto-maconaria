@props(['titulo' => null, 'metaDescricao' => null])

@php
$configuracaoInstitucional = \App\Models\ConfiguracaoInstitucional::atual();
$logotipoSite = $configuracaoInstitucional->logotipo
    ? asset('storage/'.$configuracaoInstitucional->logotipo)
    : asset('images/logo-loja.png');

$ordemPaginasInstitucionais = [
    'sobre-nos' => 10,
    'nossa-historia' => 15,
    'o-que-e-maconaria' => 20,
    'maconaria-para-jovens' => 30,
    'mudando-o-cidadao' => 40,
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

// Endereço em uma única linha, usado para montar os links de rota do
// rodapé (Waze e Google Maps abrem já buscando por este texto).
$enderecoMapa = $configuracaoInstitucional->endereco_rodape
    ? trim(preg_replace('/\s+/', ' ', $configuracaoInstitucional->endereco_rodape))
    : null;

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
<body
    class="min-h-screen bg-white font-siteSans text-[17px] leading-relaxed text-brand-ink antialiased"
    x-data="{ menuAberto: false, mostrarBotaoTopo: false }"
    @scroll.window="mostrarBotaoTopo = window.scrollY > 400"
>
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
                                <a href="{{ $paginaMenu->urlPublica() }}" class="block px-4 py-2.5 text-[0.95rem] font-medium hover:bg-brand-paperSoft">{{ $paginaMenu->titulo }}</a>
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
                <a href="{{ $paginaMenu->urlPublica() }}" class="block border-b border-brand-navy/10 py-3 text-base font-semibold text-brand-ink">{{ $paginaMenu->titulo }}</a>
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
                    <a href="{{ route('home') }}" class="flex items-center gap-3">
                        <img src="{{ $logotipoSite }}" alt="Selo da {{ $configuracaoInstitucional->nome() }}" class="h-11 w-11 shrink-0 object-contain">
                        <span class="font-siteDisplay text-lg font-bold leading-tight text-white">{{ $configuracaoInstitucional->nome() }}</span>
                    </a>

                    @if ($configuracaoInstitucional->endereco_rodape)
                        <p class="mt-3 max-w-xs whitespace-pre-line text-[0.95rem] leading-relaxed text-white/60">{{ $configuracaoInstitucional->endereco_rodape }}</p>

                        <div class="mt-3 flex flex-wrap items-center gap-3">
                            <a
                                href="https://waze.com/ul?q={{ urlencode($enderecoMapa) }}&navigate=yes"
                                target="_blank" rel="noopener noreferrer"
                                class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3.5 py-2 text-sm font-semibold text-white/80 transition hover:bg-white/20 hover:text-white"
                            >
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2C7.03 2 3 5.5 3 9.83c0 3.29 2.34 6.13 5.61 7.27.3.1.5.4.46.72l-.3 2.2a.75.75 0 0 0 1.09.77l2.6-1.42c.2-.11.44-.14.66-.09.28.06.57.09.88.09 4.97 0 9-3.5 9-7.83S16.97 2 12 2Zm-3.5 6.5a1.25 1.25 0 1 1 0 2.5 1.25 1.25 0 0 1 0-2.5Zm7 0a1.25 1.25 0 1 1 0 2.5 1.25 1.25 0 0 1 0-2.5Zm-6.68 4.62a.75.75 0 0 1 1.05.14 3.63 3.63 0 0 0 5.86 0 .75.75 0 1 1 1.2.9 5.13 5.13 0 0 1-8.26 0 .75.75 0 0 1 .15-1.04Z" /></svg>
                                Waze
                            </a>

                            <a
                                href="https://www.google.com/maps/search/?api=1&query={{ urlencode($enderecoMapa) }}"
                                target="_blank" rel="noopener noreferrer"
                                class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3.5 py-2 text-sm font-semibold text-white/80 transition hover:bg-white/20 hover:text-white"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                </svg>
                                Google Maps
                            </a>
                        </div>
                    @endif
                </div>

                @if ($paginasInstitucionaisMenu->isNotEmpty())
                    <div>
                        <h2 class="text-[0.95rem] font-bold text-white">Institucional</h2>
                        <ul class="mt-4 flex flex-col gap-2.5 text-[0.95rem]">
                            @foreach ($paginasInstitucionaisMenu as $paginaMenu)
                                <li><a href="{{ $paginaMenu->urlPublica() }}" class="hover:text-white hover:underline">{{ $paginaMenu->titulo }}</a></li>
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
                        <li><a href="{{ route('contato.mostrar') }}" class="hover:text-white hover:underline">Contato</a></li>
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
                            <li class="flex flex-wrap items-center gap-3 pt-1">
                                @if ($configuracaoInstitucional->facebook_url)
                                    <a href="{{ $configuracaoInstitucional->facebook_url }}" target="_blank" rel="noopener noreferrer" class="flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-white/80 transition hover:bg-white/20 hover:text-white" aria-label="Facebook">
                                        <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M9.101 23.691v-7.98H6.627v-3.667h2.474v-1.58c0-4.085 1.848-5.978 5.858-5.978.401 0 .955.042 1.468.103a8.68 8.68 0 0 1 1.141.195v3.325a8.623 8.623 0 0 0-.653-.036 26.805 26.805 0 0 0-.733-.009c-.707 0-1.259.096-1.675.309a1.686 1.686 0 0 0-.679.622c-.258.42-.374.995-.374 1.752v1.297h3.919l-.386 2.103-.287 1.564h-3.246v8.245C19.396 23.238 24 18.179 24 12.044c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.628 3.874 10.35 9.101 11.647Z" /></svg>
                                    </a>
                                @endif
                                @if ($configuracaoInstitucional->instagram_url)
                                    <a href="{{ $configuracaoInstitucional->instagram_url }}" target="_blank" rel="noopener noreferrer" class="flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-white/80 transition hover:bg-white/20 hover:text-white" aria-label="Instagram">
                                        <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7.0301.084c-1.2768.0602-2.1487.264-2.911.5634-.7888.3075-1.4575.72-2.1228 1.3877-.6652.6677-1.075 1.3368-1.3802 2.127-.2954.7638-.4956 1.6365-.552 2.914-.0564 1.2775-.0689 1.6882-.0626 4.947.0062 3.2586.0206 3.6671.0825 4.9473.061 1.2765.264 2.1482.5635 2.9107.308.7889.72 1.4573 1.388 2.1228.6679.6655 1.3365 1.0743 2.1285 1.38.7632.295 1.6361.4961 2.9134.552 1.2773.056 1.6884.069 4.9462.0627 3.2578-.0062 3.668-.0207 4.9478-.0814 1.28-.0607 2.147-.2652 2.9098-.5633.7889-.3086 1.4578-.72 2.1228-1.3881.665-.6682 1.0745-1.3378 1.3795-2.1284.2957-.7632.4966-1.636.552-2.9124.056-1.2809.0692-1.6898.063-4.948-.0063-3.2583-.021-3.6668-.0817-4.9465-.0607-1.2797-.264-2.1487-.5633-2.9117-.3084-.7889-.72-1.4568-1.3876-2.1228C21.2982 1.33 20.628.9208 19.8378.6165 19.074.321 18.2017.1197 16.9244.0645 15.6471.0093 15.236-.005 11.977.0014 8.718.0076 8.31.0215 7.0301.0839m.1402 21.6932c-1.17-.0509-1.8053-.2453-2.2287-.408-.5606-.216-.96-.4771-1.3819-.895-.422-.4178-.6811-.8186-.9-1.378-.1644-.4234-.3624-1.058-.4171-2.228-.0595-1.2645-.072-1.6442-.079-4.848-.007-3.2037.0053-3.583.0607-4.848.05-1.169.2456-1.805.408-2.2282.216-.5613.4762-.96.895-1.3816.4188-.4217.8184-.6814 1.3783-.9003.423-.1651 1.0575-.3614 2.227-.4171 1.2655-.06 1.6447-.072 4.848-.079 3.2033-.007 3.5835.005 4.8495.0608 1.169.0508 1.8053.2445 2.228.408.5608.216.96.4754 1.3816.895.4217.4194.6816.8176.9005 1.3787.1653.4217.3617 1.056.4169 2.2263.0602 1.2655.0739 1.645.0796 4.848.0058 3.203-.0055 3.5834-.061 4.848-.051 1.17-.245 1.8055-.408 2.2294-.216.5604-.4763.96-.8954 1.3814-.419.4215-.8181.6811-1.3783.9-.4224.1649-1.0577.3617-2.2262.4174-1.2656.0595-1.6448.072-4.8493.079-3.2045.007-3.5825-.006-4.848-.0608M16.953 5.5864A1.44 1.44 0 1 0 18.39 4.144a1.44 1.44 0 0 0-1.437 1.4424M5.8385 12.012c.0067 3.4032 2.7706 6.1557 6.173 6.1493 3.4026-.0065 6.157-2.7701 6.1506-6.1733-.0065-3.4032-2.771-6.1565-6.174-6.1498-3.403.0067-6.156 2.771-6.1496 6.1738M8 12.0077a4 4 0 1 1 4.008 3.9921A3.9996 3.9996 0 0 1 8 12.0077" /></svg>
                                    </a>
                                @endif
                                @if ($configuracaoInstitucional->twitter_url)
                                    <a href="{{ $configuracaoInstitucional->twitter_url }}" target="_blank" rel="noopener noreferrer" class="flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-white/80 transition hover:bg-white/20 hover:text-white" aria-label="X (Twitter)">
                                        <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M14.234 10.162 22.977 0h-2.072l-7.591 8.824L7.251 0H.258l9.168 13.343L.258 24H2.33l8.016-9.318L16.749 24h6.993zm-2.837 3.299-.929-1.329L3.076 1.56h3.182l5.965 8.532.929 1.329 7.754 11.09h-3.182z" /></svg>
                                    </a>
                                @endif
                                @if ($configuracaoInstitucional->tiktok_url)
                                    <a href="{{ $configuracaoInstitucional->tiktok_url }}" target="_blank" rel="noopener noreferrer" class="flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-white/80 transition hover:bg-white/20 hover:text-white" aria-label="TikTok">
                                        <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z" /></svg>
                                    </a>
                                @endif
                            </li>
                        @endif
                    </ul>
                </div>
            </div>

            <div class="mt-10 flex flex-col gap-3 border-t border-white/15 pt-6 text-[0.9rem] text-white/50 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-col gap-1">
                    <p>&copy; {{ now()->year }} {{ $configuracaoInstitucional->nome() }}. Todos os direitos reservados.</p>
                    <p>Desenvolvido por <a href="https://jmfsystem.com/" target="_blank" rel="noopener noreferrer" class="font-semibold hover:text-white hover:underline">José Marcio Ferreira da Silva</a></p>
                </div>

                @if ($paginaPoliticaPrivacidade || $paginaTermosUso)
                    <div class="flex gap-5">
                        @if ($paginaPoliticaPrivacidade)
                            <a href="{{ $paginaPoliticaPrivacidade->urlPublica() }}" class="hover:text-white hover:underline">{{ $paginaPoliticaPrivacidade->titulo }}</a>
                        @endif

                        @if ($paginaTermosUso)
                            <a href="{{ $paginaTermosUso->urlPublica() }}" class="hover:text-white hover:underline">{{ $paginaTermosUso->titulo }}</a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </footer>

    <button
        type="button"
        x-show="mostrarBotaoTopo"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        @click="window.scrollTo({ top: 0, behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' })"
        class="fixed bottom-6 right-6 z-40 flex h-12 w-12 items-center justify-center rounded-full bg-brand-navy text-white shadow-lg transition hover:bg-brand-navyDeep"
        aria-label="Voltar ao topo da página"
    >
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" />
        </svg>
    </button>
</body>
</html>
