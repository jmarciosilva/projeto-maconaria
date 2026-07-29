<div class="flex h-16 items-center px-6 text-lg font-semibold">
    {{ config('app.name') }}
</div>

<nav class="space-y-1 px-3 py-4 text-sm">
    <a href="{{ route('area-restrita') }}" class="block rounded-md px-3 py-2 font-medium text-blue-100 hover:bg-blue-900">
        Voltar à Área Restrita
    </a>

    @can('usuarios.visualizar')
        <a href="{{ route('admin.usuarios.index') }}" class="block rounded-md px-3 py-2 font-medium {{ request()->routeIs('admin.usuarios.*') ? 'bg-blue-900 text-white' : 'text-blue-100 hover:bg-blue-900' }}">
            Usuários
        </a>
    @endcan

    @can('perfis.visualizar')
        <a href="{{ route('admin.perfis.index') }}" class="block rounded-md px-3 py-2 font-medium {{ request()->routeIs('admin.perfis.*') ? 'bg-blue-900 text-white' : 'text-blue-100 hover:bg-blue-900' }}">
            Perfis e Permissões
        </a>
    @endcan
</nav>
