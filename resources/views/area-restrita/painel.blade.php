<x-layouts.restrito>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Painel</h2>
    </x-slot>

    <div class="rounded-lg bg-white p-6 shadow-sm">
        <p class="text-gray-700">
            Bem-vindo(a), {{ auth()->user()->name }}. Esta é a área restrita aos usuários
            autenticados e Irmãos da Loja. Os módulos (notícias, eventos, calendário de
            sessões, mural da Loja etc.) serão disponibilizados aqui nas próximas fases.
        </p>
    </div>
</x-layouts.restrito>
