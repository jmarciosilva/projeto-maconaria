@props(['variante' => 'primario', 'tipo' => 'button'])

@php
$estilos = [
    'primario' => 'bg-blue-900 text-white hover:bg-blue-800 focus-visible:outline-blue-900',
    'secundario' => 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 focus-visible:outline-gray-400',
    'perigo' => 'bg-red-700 text-white hover:bg-red-800 focus-visible:outline-red-700',
];
@endphp

<button
    type="{{ $tipo }}"
    {{ $attributes->merge(['class' => 'inline-flex items-center justify-center gap-2 rounded-md px-4 py-2 text-sm font-semibold shadow-sm transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 disabled:opacity-50 disabled:cursor-not-allowed '.($estilos[$variante] ?? $estilos['primario'])]) }}
>
    {{ $slot }}
</button>
