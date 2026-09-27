@props(['titulo' => null])

@php
$configuracaoInstitucional = \App\Models\ConfiguracaoInstitucional::atual();
$logotipoSite = $configuracaoInstitucional->logotipo
    ? asset('storage/'.$configuracaoInstitucional->logotipo)
    : asset('images/logo-loja.png');

// O usuário vê o Painel Administrativo no menu assim que tiver qualquer
// permissão de gestão — quem não tem nenhuma (Irmão comum, Visitante
// Autorizado) simplesmente não vê o link, sem precisar de uma permissão
// específica para isso. O Administrador tem acesso total via Gate::before
// em AppServiceProvider (não depende só das permissões sincronizadas), por
// isso precisa da checagem de perfil à parte.
$podeAcessarAdmin = auth()->user()->hasRole('Administrador') || auth()->user()->getAllPermissions()->isNotEmpty();
@endphp

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $titulo ? $titulo.' — '.$configuracaoInstitucional->nome() : 'Mural da Loja — '.$configuracaoInstitucional->nome() }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body
    class="min-h-screen bg-brand-paperSoft font-siteSans text-[17px] leading-relaxed text-brand-ink antialiased"
    x-data="{ menuAberto: false, mostrarBotaoTopo: false }"
    @scroll.window="mostrarBotaoTopo = window.scrollY > 400"
>
    <header class="border-b border-brand-navy/10 bg-white">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-5 py-3.5 lg:px-8">
            <a href="{{ route('area-restrita') }}" class="flex min-w-0 shrink items-center gap-3">
                <img src="{{ $logotipoSite }}" alt="Selo da {{ $configuracaoInstitucional->nome() }}" class="h-14 w-14 shrink-0 object-contain sm:h-16 sm:w-16">
                <span class="line-clamp-2 font-siteDisplay text-sm font-bold leading-tight text-brand-navy sm:text-base">{{ $configuracaoInstitucional->nome() }}</span>
            </a>

            <nav class="hidden shrink-0 items-center gap-5 text-[0.98rem] font-semibold text-brand-inkSoft xl:flex" aria-label="Navegação da área restrita">
                <a href="{{ route('home') }}" class="border-b-[3px] border-transparent pb-1 hover:text-brand-navy">Home</a>

                <a href="{{ route('area-restrita') }}" @if (request()->routeIs('area-restrita')) aria-current="page" @endif class="border-b-[3px] border-transparent pb-1 hover:text-brand-navy {{ request()->routeIs('area-restrita') ? 'border-brand-navy text-brand-navy' : '' }}">
                    Mural da Loja
                </a>

                @if ($podeAcessarAdmin)
                    <a href="{{ route('admin.dashboard') }}" class="border-b-[3px] border-transparent pb-1 hover:text-brand-navy">Painel Administrativo</a>
                @endif

                <span class="flex cursor-not-allowed items-center gap-1.5 border-b-[3px] border-transparent pb-1 text-brand-inkSoft/50" title="Em breve">
                    Rede Social da Loja
                    <span class="rounded-full bg-brand-navy/10 px-1.5 py-0.5 text-[0.65rem] font-bold uppercase tracking-wide text-brand-navy/60">Em breve</span>
                </span>

                <span class="flex cursor-not-allowed items-center gap-1.5 border-b-[3px] border-transparent pb-1 text-brand-inkSoft/50" title="Em breve">
                    AVA
                    <span class="rounded-full bg-brand-navy/10 px-1.5 py-0.5 text-[0.65rem] font-bold uppercase tracking-wide text-brand-navy/60">Em breve</span>
                </span>
            </nav>

            <div class="flex shrink-0 items-center gap-3">
                <a href="{{ route('profile.edit') }}" class="hidden text-[0.95rem] font-semibold text-brand-inkSoft hover:text-brand-navy xl:inline-flex">{{ auth()->user()->name }}</a>

                <form method="POST" action="{{ route('logout') }}" class="hidden xl:block">
                    @csrf
                    <button type="submit" class="rounded-md bg-brand-navy px-5 py-2.5 text-[0.95rem] font-bold text-white transition hover:bg-brand-navyDeep">Sair</button>
                </form>

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

        <nav x-show="menuAberto" x-cloak class="border-t border-brand-navy/10 bg-brand-paperSoft px-5 py-2 xl:hidden" aria-label="Navegação da área restrita (mobile)">
            <a href="{{ route('home') }}" class="block border-b border-brand-navy/10 py-3 text-base font-semibold text-brand-ink">Home</a>
            <a href="{{ route('area-restrita') }}" class="block border-b border-brand-navy/10 py-3 text-base font-semibold {{ request()->routeIs('area-restrita') ? 'text-brand-navy' : 'text-brand-ink' }}">Mural da Loja</a>
            @if ($podeAcessarAdmin)
                <a href="{{ route('admin.dashboard') }}" class="block border-b border-brand-navy/10 py-3 text-base font-semibold text-brand-ink">Painel Administrativo</a>
            @endif
            <span class="flex items-center gap-1.5 border-b border-brand-navy/10 py-3 text-base font-semibold text-brand-ink/40">
                Rede Social da Loja
                <span class="rounded-full bg-brand-navy/10 px-1.5 py-0.5 text-[0.65rem] font-bold uppercase tracking-wide text-brand-navy/60">Em breve</span>
            </span>
            <span class="flex items-center gap-1.5 border-b border-brand-navy/10 py-3 text-base font-semibold text-brand-ink/40">
                AVA
                <span class="rounded-full bg-brand-navy/10 px-1.5 py-0.5 text-[0.65rem] font-bold uppercase tracking-wide text-brand-navy/60">Em breve</span>
            </span>
            <a href="{{ route('profile.edit') }}" class="block border-b border-brand-navy/10 py-3 text-base font-semibold text-brand-ink">Meu Perfil</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="block w-full py-3 text-left text-base font-bold text-brand-navy">Sair</button>
            </form>
        </nav>
    </header>

    <main class="mx-auto max-w-6xl px-5 py-8 lg:px-8">
        @if (session('sucesso'))
            <div class="mb-4"><x-ui.alert tipo="sucesso">{{ session('sucesso') }}</x-ui.alert></div>
        @endif

        @if (session('erro'))
            <div class="mb-4"><x-ui.alert tipo="erro">{{ session('erro') }}</x-ui.alert></div>
        @endif

        @isset($header)
            <div class="mb-6">{{ $header }}</div>
        @endisset

        {{ $slot }}
    </main>

    <footer class="relative mt-16 overflow-hidden bg-gradient-to-b from-brand-navy to-brand-navyDeep text-white/80">
        <div class="pointer-events-none absolute inset-0 flex select-none items-center justify-center opacity-[0.05]" aria-hidden="true">
            <img src="{{ $logotipoSite }}" alt="" class="h-64 w-64 object-contain sm:h-80 sm:w-80">
        </div>

        <div class="relative mx-auto max-w-6xl px-5 py-12 lg:px-8">
            <div class="grid gap-8 sm:grid-cols-[1.4fr_1fr]">
                <a href="{{ route('area-restrita') }}" class="flex items-center gap-4">
                    <img src="{{ $logotipoSite }}" alt="Selo da {{ $configuracaoInstitucional->nome() }}" class="h-28 w-28 shrink-0 object-contain">
                    <span class="font-siteDisplay text-lg font-bold leading-tight text-white">Augusta e Respeitável Loja Simbólica Ferraz de Vasconcelos n° 2516 - Benfeitora da Ordem</span>
                </a>

                <div>
                    <h2 class="text-sm font-bold uppercase tracking-wide text-white">Links Rápidos</h2>
                    <span class="mt-2 block h-0.5 w-8 rounded-full bg-brand-sky/60"></span>
                    <ul class="mt-4 flex flex-col gap-2.5 text-[0.95rem]">
                        <li><a href="{{ route('home') }}" class="hover:text-white hover:underline">Home</a></li>
                        <li><a href="{{ route('area-restrita') }}" class="hover:text-white hover:underline">Mural da Loja</a></li>
                        @if ($podeAcessarAdmin)
                            <li><a href="{{ route('admin.dashboard') }}" class="hover:text-white hover:underline">Painel Administrativo</a></li>
                        @endif
                        <li><a href="{{ route('profile.edit') }}" class="hover:text-white hover:underline">Meu Perfil</a></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="hover:text-white hover:underline">Sair</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="mt-10 flex flex-col gap-1 border-t border-white/15 pt-5 text-[0.85rem] text-white/50">
                <p>&copy; {{ now()->year }} {{ $configuracaoInstitucional->nome() }}. Todos os direitos reservados.</p>
                <p>Desenvolvido por <a href="https://jmfsystem.com/" target="_blank" rel="noopener noreferrer" class="font-semibold hover:text-white hover:underline">José Marcio Ferreira da Silva</a></p>
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
        class="fixed bottom-6 right-6 z-40 flex h-12 w-12 items-center justify-center rounded-full border border-brand-navy/10 bg-white text-brand-navy shadow-lg ring-1 ring-black/5 transition hover:bg-brand-paperSoft"
        aria-label="Voltar ao topo da página"
    >
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" />
        </svg>
    </button>
</body>
</html>
