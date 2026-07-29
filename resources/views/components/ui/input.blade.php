@props(['rotulo', 'nome', 'tipo' => 'text', 'valor' => null, 'erro' => null, 'obrigatorio' => false])

<div>
    <label for="{{ $nome }}" class="block text-sm font-medium text-gray-700">
        {{ $rotulo }} @if ($obrigatorio) <span class="text-red-600">*</span> @endif
    </label>

    <input
        type="{{ $tipo }}"
        id="{{ $nome }}"
        name="{{ $nome }}"
        value="{{ old($nome, $valor) }}"
        {{ $attributes->merge(['class' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm '.($erro ? 'border-red-400' : '')]) }}
        @if ($obrigatorio) required @endif
    >

    @if ($erro)
        <p class="mt-1 text-sm text-red-600">{{ $erro }}</p>
    @endif
</div>
