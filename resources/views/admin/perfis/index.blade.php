<x-layouts.admin titulo="Perfis e Permissões">
    <p class="mb-4 text-sm text-gray-600">
        Consulta dos perfis e permissões cadastrados. A edição destes perfis pelo painel
        será disponibilizada em uma fase futura (ver docs/MODULOS.md).
    </p>

    <div class="space-y-4">
        @foreach ($perfis as $perfil)
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <h3 class="font-semibold text-gray-900">{{ $perfil->name }}</h3>
                <div class="mt-2 flex flex-wrap gap-2">
                    @forelse ($perfil->permissions as $permissao)
                        <x-ui.badge>{{ $permissao->name }}</x-ui.badge>
                    @empty
                        <span class="text-sm text-gray-500">
                            @if ($perfil->name === 'Superadministrador')
                                Acesso total (concedido automaticamente, independente de permissões atribuídas).
                            @else
                                Nenhuma permissão atribuída.
                            @endif
                        </span>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</x-layouts.admin>
