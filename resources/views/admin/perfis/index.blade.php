<x-layouts.admin titulo="Perfis e Permissões">
    <p class="mb-4 text-sm text-gray-600">
        Consulta dos perfis e permissões cadastrados. A edição destes perfis pelo painel
        será disponibilizada em uma fase futura (ver docs/MODULOS.md).
    </p>

    <div class="space-y-4">
        @foreach ($perfis as $perfil)
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <h3 class="font-semibold text-gray-900">{{ $perfil->name }}</h3>

                @if ($perfil->name === 'Administrador')
                    <p class="mt-1 text-xs text-gray-500">Além das permissões abaixo, este perfil tem acesso total garantido automaticamente (independente da lista), para nunca ficar travado por uma permissão esquecida.</p>
                @endif

                <div class="mt-2 flex flex-wrap gap-2">
                    @forelse ($perfil->permissions as $permissao)
                        <x-ui.badge>{{ $permissao->name }}</x-ui.badge>
                    @empty
                        <span class="text-sm text-gray-500">Nenhuma permissão atribuída.</span>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</x-layouts.admin>
