@props(['titulo' => null])

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $titulo ? $titulo.' — Área Restrita' : 'Área Restrita' }} — {{ config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-100 font-sans text-gray-900 antialiased" x-data="{ menuAberto: false }">
    <nav class="border-b border-gray-200 bg-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
            <div class="flex items-center gap-8">
                <a href="{{ route('area-restrita') }}" class="text-lg font-semibold text-blue-950">{{ config('app.name') }}</a>

                <div class="hidden gap-6 text-sm font-medium sm:flex">
                    <a href="{{ route('area-restrita') }}" class="{{ request()->routeIs('area-restrita') ? 'text-blue-900' : 'text-gray-600 hover:text-gray-900' }}">Painel</a>

                    @can('usuarios.visualizar')
                        <a href="{{ route('admin.usuarios.index') }}" class="text-gray-600 hover:text-gray-900">Painel Administrativo</a>
                    @endcan
                </div>
            </div>

            <div class="hidden items-center gap-4 sm:flex">
                <a href="{{ route('profile.edit') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">{{ auth()->user()->name }}</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm font-medium text-gray-600 hover:text-gray-900">Sair</button>
                </form>
            </div>

            <button
                type="button"
                class="inline-flex items-center justify-center rounded-md p-2 text-gray-500 sm:hidden"
                @click="menuAberto = !menuAberto"
                aria-label="Abrir menu"
                :aria-expanded="menuAberto"
            >
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                </svg>
            </button>
        </div>

        <div x-show="menuAberto" x-cloak class="border-t border-gray-200 px-4 py-3 sm:hidden">
            <a href="{{ route('area-restrita') }}" class="block py-2 text-sm font-medium text-gray-700">Painel</a>
            <a href="{{ route('profile.edit') }}" class="block py-2 text-sm font-medium text-gray-700">Meu Perfil</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="block w-full py-2 text-left text-sm font-medium text-gray-700">Sair</button>
            </form>
        </div>
    </nav>

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
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
</body>
</html>
