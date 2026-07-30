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
<body class="min-h-screen bg-white font-sans text-gray-900 antialiased" x-data="{ menuAberto: false }">
    <header class="border-b border-gray-200 bg-blue-950 text-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-3 text-lg font-semibold tracking-wide">
                <img src="{{ $logotipoSite }}" alt="{{ $configuracaoInstitucional->nome() }}" class="h-10 w-10 rounded-full object-contain">
                {{ $configuracaoInstitucional->nome() }}
            </a>

            <nav class="hidden items-center gap-6 text-sm font-medium sm:flex">
                <a href="{{ route('home') }}" class="hover:text-blue-200">Início</a>
                <a href="{{ route('noticias.index') }}" class="hover:text-blue-200">Notícias</a>
                <a href="{{ route('eventos.index') }}" class="hover:text-blue-200">Eventos</a>
                <a href="{{ route('calendario') }}" class="hover:text-blue-200">Calendário</a>
                <a href="{{ route('mural.index') }}" class="hover:text-blue-200">Mural</a>
                <a href="{{ route('galeria.index') }}" class="hover:text-blue-200">Galeria</a>

                @if ($paginasInstitucionaisMenu->isNotEmpty())
                    <div x-data="{ institucionalAberto: false }" class="relative" @click.outside="institucionalAberto = false">
                        <button type="button" @click="institucionalAberto = !institucionalAberto" class="flex items-center gap-1 hover:text-blue-200" :aria-expanded="institucionalAberto">
                            Institucional
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                        </button>

                        <div x-show="institucionalAberto" x-cloak class="absolute left-0 z-10 mt-2 w-72 rounded-md bg-white py-1 text-gray-700 shadow-lg ring-1 ring-black/5">
                            @foreach ($paginasInstitucionaisMenu as $paginaMenu)
                                <a href="{{ $urlPaginaInstitucional($paginaMenu) }}" class="block px-4 py-2 hover:bg-gray-50">{{ $paginaMenu->titulo }}</a>
                            @endforeach
                        </div>
                    </div>
                @endif

                @auth
                    <a href="{{ route('area-restrita') }}" class="hover:text-blue-200">Área Restrita</a>
                @else
                    <a href="{{ route('login') }}" class="hover:text-blue-200">Entrar</a>
                @endauth
            </nav>

            <button
                type="button"
                class="inline-flex items-center justify-center rounded-md p-2 text-white sm:hidden"
                @click="menuAberto = !menuAberto"
                aria-label="Abrir menu"
                :aria-expanded="menuAberto"
            >
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                </svg>
            </button>
        </div>

        <nav x-show="menuAberto" x-cloak class="border-t border-blue-900 px-4 py-3 sm:hidden">
            <a href="{{ route('home') }}" class="block py-2 text-sm font-medium">Início</a>
            <a href="{{ route('noticias.index') }}" class="block py-2 text-sm font-medium">Notícias</a>
            <a href="{{ route('eventos.index') }}" class="block py-2 text-sm font-medium">Eventos</a>
            <a href="{{ route('calendario') }}" class="block py-2 text-sm font-medium">Calendário</a>
            <a href="{{ route('mural.index') }}" class="block py-2 text-sm font-medium">Mural</a>
            <a href="{{ route('galeria.index') }}" class="block py-2 text-sm font-medium">Galeria</a>
            @foreach ($paginasInstitucionaisMenu as $paginaMenu)
                <a href="{{ $urlPaginaInstitucional($paginaMenu) }}" class="block py-2 text-sm font-medium">{{ $paginaMenu->titulo }}</a>
            @endforeach
            @auth
                <a href="{{ route('area-restrita') }}" class="block py-2 text-sm font-medium">Área Restrita</a>
            @else
                <a href="{{ route('login') }}" class="block py-2 text-sm font-medium">Entrar</a>
            @endauth
        </nav>
    </header>

    <main>
        @if (session('sucesso'))
            <div class="mx-auto mt-4 max-w-7xl px-4 sm:px-6 lg:px-8">
                <x-ui.alert tipo="sucesso">{{ session('sucesso') }}</x-ui.alert>
            </div>
        @endif

        @if (session('erro'))
            <div class="mx-auto mt-4 max-w-7xl px-4 sm:px-6 lg:px-8">
                <x-ui.alert tipo="erro">{{ session('erro') }}</x-ui.alert>
            </div>
        @endif

        {{ $slot }}
    </main>

    <footer class="mt-16 border-t border-gray-200 bg-gray-50">
        <div class="mx-auto max-w-7xl px-4 py-8 text-sm text-gray-600 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="font-semibold text-gray-800">{{ $configuracaoInstitucional->nome() }}</p>

                    @if ($configuracaoInstitucional->endereco_rodape)
                        <p class="mt-1 whitespace-pre-line">{{ $configuracaoInstitucional->endereco_rodape }}</p>
                    @endif

                    @if ($configuracaoInstitucional->telefone_institucional)
                        <p class="mt-1">{{ $configuracaoInstitucional->telefone_institucional }}</p>
                    @endif

                    @if ($configuracaoInstitucional->email_institucional)
                        <p class="mt-1">
                            <a href="mailto:{{ $configuracaoInstitucional->email_institucional }}" class="hover:text-blue-900 hover:underline">
                                {{ $configuracaoInstitucional->email_institucional }}
                            </a>
                        </p>
                    @endif
                </div>

                @if ($configuracaoInstitucional->possuiRedesSociais())
                    <div class="flex items-center gap-4">
                        @if ($configuracaoInstitucional->facebook_url)
                            <a href="{{ $configuracaoInstitucional->facebook_url }}" target="_blank" rel="noopener noreferrer" class="hover:text-blue-900" aria-label="Facebook">Facebook</a>
                        @endif

                        @if ($configuracaoInstitucional->instagram_url)
                            <a href="{{ $configuracaoInstitucional->instagram_url }}" target="_blank" rel="noopener noreferrer" class="hover:text-blue-900" aria-label="Instagram">Instagram</a>
                        @endif

                        @if ($configuracaoInstitucional->twitter_url)
                            <a href="{{ $configuracaoInstitucional->twitter_url }}" target="_blank" rel="noopener noreferrer" class="hover:text-blue-900" aria-label="Twitter / X">Twitter / X</a>
                        @endif

                        @if ($configuracaoInstitucional->tiktok_url)
                            <a href="{{ $configuracaoInstitucional->tiktok_url }}" target="_blank" rel="noopener noreferrer" class="hover:text-blue-900" aria-label="TikTok">TikTok</a>
                        @endif
                    </div>
                @endif
            </div>

            <div class="mt-6 flex flex-col gap-2 border-t border-gray-200 pt-4 sm:flex-row sm:items-center sm:justify-between">
                <p>&copy; {{ now()->year }} {{ $configuracaoInstitucional->nome() }}. Todos os direitos reservados.</p>

                @if ($paginaPoliticaPrivacidade || $paginaTermosUso)
                    <div class="flex gap-4">
                        @if ($paginaPoliticaPrivacidade)
                            <a href="{{ $urlPaginaInstitucional($paginaPoliticaPrivacidade) }}" class="hover:text-blue-900 hover:underline">{{ $paginaPoliticaPrivacidade->titulo }}</a>
                        @endif

                        @if ($paginaTermosUso)
                            <a href="{{ $urlPaginaInstitucional($paginaTermosUso) }}" class="hover:text-blue-900 hover:underline">{{ $paginaTermosUso->titulo }}</a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </footer>
</body>
</html>
