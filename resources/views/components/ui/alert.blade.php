@props(['tipo' => 'info'])

@php
$estilos = [
    'sucesso' => 'bg-green-50 text-green-800 border-green-200',
    'erro' => 'bg-red-50 text-red-800 border-red-200',
    'aviso' => 'bg-amber-50 text-amber-800 border-amber-200',
    'info' => 'bg-blue-50 text-blue-800 border-blue-200',
];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-md border px-4 py-3 text-sm '.($estilos[$tipo] ?? $estilos['info'])]) }} role="alert">
    {{ $slot }}
</div>
