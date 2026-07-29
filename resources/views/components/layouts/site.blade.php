@props(['titulo' => null])

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $titulo ? $titulo.' — '.config('app.name') : config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white font-sans text-gray-900 antialiased" x-data="{ menuAberto: false }">
    <header class="border-b border-gray-200 bg-blue-950 text-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-3 text-lg font-semibold tracking-wide">
                {{ config('app.name') }}
            </a>

            <nav class="hidden items-center gap-6 text-sm font-medium sm:flex">
                <a href="{{ route('home') }}" class="hover:text-blue-200">Início</a>

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
            <p>&copy; {{ now()->year }} {{ config('app.name') }}. Todos os direitos reservados.</p>
        </div>
    </footer>
</body>
</html>
