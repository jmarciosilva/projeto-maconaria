@props(['rotulo', 'nome', 'opcoes' => [], 'valor' => null, 'erro' => null, 'obrigatorio' => false])

<div>
    <label for="{{ $nome }}" class="block text-sm font-medium text-gray-700">
        {{ $rotulo }} @if ($obrigatorio) <span class="text-red-600">*</span> @endif
    </label>

    <select
        id="{{ $nome }}"
        name="{{ $nome }}"
        {{ $attributes->merge(['class' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm '.($erro ? 'border-red-400' : '')]) }}
        @if ($obrigatorio) required @endif
    >
        @foreach ($opcoes as $chave => $rotuloOpcao)
            <option value="{{ $chave }}" @selected((string) old($nome, $valor) === (string) $chave)>{{ $rotuloOpcao }}</option>
        @endforeach
    </select>

    @if ($erro)
        <p class="mt-1 text-sm text-red-600">{{ $erro }}</p>
    @endif
</div>
