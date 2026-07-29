<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="flex min-h-screen flex-col items-center justify-center bg-[#14213D] bg-gradient-to-br from-[#14213D] to-[#0B1526] px-4 pt-6 sm:pt-0">
            <div class="flex flex-col items-center">
                <a href="/">
                    <img src="{{ asset('images/logo-loja.png') }}" alt="{{ config('app.name') }}" class="h-24 w-24 rounded-full object-contain ring-4 ring-[#C9A227]/60">
                </a>
                <p class="mt-4 text-center text-sm font-medium tracking-wide text-[#C9A227]">
                    {{ config('app.name') }}
                </p>
            </div>

            <div class="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
