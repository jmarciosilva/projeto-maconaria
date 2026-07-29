@props(['titulo' => 'Nenhum registro encontrado', 'descricao' => null])

<div class="flex flex-col items-center justify-center rounded-lg border border-dashed border-gray-300 px-6 py-12 text-center">
    <svg class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
    </svg>
    <h3 class="mt-3 text-sm font-semibold text-gray-900">{{ $titulo }}</h3>
    @if ($descricao)
        <p class="mt-1 text-sm text-gray-500">{{ $descricao }}</p>
    @endif
    @isset($acao)
        <div class="mt-4">{{ $acao }}</div>
    @endisset
</div>
