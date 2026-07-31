@props(['titulo' => null])

@php
$ajuda = \App\Support\Ajuda\ConteudoAjuda::paraRota(request()->route()?->getName());
@endphp

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $titulo ? $titulo.' — Painel Administrativo' : 'Painel Administrativo' }} — {{ config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body
    class="min-h-screen bg-gray-100 font-sans text-gray-900 antialiased"
    x-data="{ menuAberto: false, sidebarColapsada: localStorage.getItem('admin-sidebar-colapsada') === '1' }"
>
    <div class="flex min-h-screen">
        {{-- Menu lateral (desktop): sticky e com rolagem própria, para que
             descer até um item no fim do menu não role a página inteira. --}}
        <aside
            class="hidden shrink-0 overflow-hidden bg-[#14213D] text-white transition-[width] duration-200 lg:sticky lg:top-0 lg:block lg:h-screen lg:overflow-y-auto"
            :class="sidebarColapsada ? 'lg:w-0' : 'lg:w-64'"
        >
            <div class="w-64">
                @include('components.layouts.partials.admin-nav')
            </div>
        </aside>

        {{-- Menu lateral (mobile, sobreposto) --}}
        <div x-show="menuAberto" x-cloak class="fixed inset-0 z-40 lg:hidden">
            <div class="fixed inset-0 bg-black/50" @click="menuAberto = false"></div>
            <aside class="relative z-50 h-full w-64 overflow-y-auto bg-[#14213D] text-white">
                @include('components.layouts.partials.admin-nav')
            </aside>
        </div>

        <div class="flex flex-1 flex-col">
            <header class="flex items-center justify-between border-b border-gray-200 bg-white px-4 py-3 sm:px-6">
                <div class="flex items-center gap-1">
                    <button
                        type="button"
                        class="inline-flex items-center justify-center rounded-md p-2 text-gray-500 lg:hidden"
                        @click="menuAberto = !menuAberto"
                        aria-label="Abrir menu"
                        :aria-expanded="menuAberto"
                    >
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                        </svg>
                    </button>

                    <button
                        type="button"
                        class="hidden items-center justify-center rounded-md p-2 text-gray-500 hover:bg-gray-100 lg:inline-flex"
                        @click="sidebarColapsada = !sidebarColapsada; localStorage.setItem('admin-sidebar-colapsada', sidebarColapsada ? '1' : '0')"
                        aria-label="Recolher ou expandir o menu lateral"
                        :aria-expanded="!sidebarColapsada"
                    >
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h16.5v13.5H3.75z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5.25v13.5" />
                        </svg>
                    </button>
                </div>

                <h1 class="text-lg font-semibold text-gray-800">{{ $titulo ?? 'Painel Administrativo' }}</h1>

                <div class="flex items-center gap-2 sm:gap-4">
                    <x-ui.ajuda-modal :titulo="$ajuda['titulo']" :itens="$ajuda['itens']" />

                    <span class="hidden text-sm text-gray-600 sm:inline">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm font-medium text-gray-600 hover:text-gray-900">Sair</button>
                    </form>
                </div>
            </header>

            <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
                @if (session('sucesso'))
                    <div class="mb-4"><x-ui.alert tipo="sucesso">{{ session('sucesso') }}</x-ui.alert></div>
                @endif

                @if (session('erro'))
                    <div class="mb-4"><x-ui.alert tipo="erro">{{ session('erro') }}</x-ui.alert></div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
