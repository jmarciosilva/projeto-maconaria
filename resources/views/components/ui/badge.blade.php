@props(['tipo' => 'neutro'])

@php
$estilos = [
    'neutro' => 'bg-gray-100 text-gray-700',
    'sucesso' => 'bg-green-100 text-green-700',
    'erro' => 'bg-red-100 text-red-700',
    'aviso' => 'bg-amber-100 text-amber-700',
];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium '.($estilos[$tipo] ?? $estilos['neutro'])]) }}>
    {{ $slot }}
</span>
