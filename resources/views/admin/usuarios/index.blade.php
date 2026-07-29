<x-layouts.admin titulo="Usuários">
    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-gray-600">Gerencie os usuários com acesso ao sistema.</p>

        @can('usuarios.criar')
            <a href="{{ route('admin.usuarios.create') }}">
                <x-ui.button>Novo usuário</x-ui.button>
            </a>
        @endcan
    </div>

    @if ($usuarios->isEmpty())
        <x-ui.empty-state titulo="Nenhum usuário cadastrado" descricao="Cadastre o primeiro usuário para começar." />
    @else
        <x-ui.table :cabecalhos="['Nome', 'E-mail', 'Status', 'Perfis', 'Último acesso', 'Ações']">
            @foreach ($usuarios as $usuario)
                <tr>
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $usuario->name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $usuario->email }}</td>
                    <td class="px-4 py-3">
                        @if ($usuario->estaBloqueado())
                            <x-ui.badge tipo="erro">Bloqueado</x-ui.badge>
                        @elseif ($usuario->status === \App\Enums\StatusUsuario::ATIVO)
                            <x-ui.badge tipo="sucesso">Ativo</x-ui.badge>
                        @else
                            <x-ui.badge tipo="neutro">Inativo</x-ui.badge>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $usuario->roles->pluck('name')->implode(', ') ?: '—' }}</td>
                    <td class="px-4 py-3 text-gray-600">
                        {{ $usuario->ultimo_acesso_em?->format('d/m/Y H:i') ?? 'Nunca acessou' }}
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex flex-wrap items-center gap-3 text-sm">
                            @can('usuarios.editar')
                                <a href="{{ route('admin.usuarios.edit', $usuario) }}" class="font-medium text-blue-800 hover:underline">Editar</a>
                            @endcan

                            @can('usuarios.excluir')
                                @if ($usuario->id !== auth()->id())
                                    @if ($usuario->status === \App\Enums\StatusUsuario::ATIVO)
                                        <x-ui.confirmation
                                            :acao="route('admin.usuarios.desativar', $usuario)"
                                            metodo="PATCH"
                                            titulo="Desativar usuário"
                                            :mensagem="'Tem certeza que deseja desativar '.$usuario->name.'?'"
                                            rotulo="Desativar"
                                        >
                                            <x-slot:gatilho>
                                                <button type="button" class="font-medium text-red-700 hover:underline">Desativar</button>
                                            </x-slot:gatilho>
                                        </x-ui.confirmation>
                                    @else
                                        <form method="POST" action="{{ route('admin.usuarios.ativar', $usuario) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="font-medium text-green-700 hover:underline">Ativar</button>
                                        </form>
                                    @endif

                                    @if ($usuario->estaBloqueado())
                                        <form method="POST" action="{{ route('admin.usuarios.desbloquear', $usuario) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="font-medium text-green-700 hover:underline">Desbloquear</button>
                                        </form>
                                    @else
                                        <x-ui.confirmation
                                            :acao="route('admin.usuarios.bloquear', $usuario)"
                                            metodo="PATCH"
                                            titulo="Bloquear usuário"
                                            :mensagem="'Tem certeza que deseja bloquear '.$usuario->name.'?'"
                                            rotulo="Bloquear"
                                        >
                                            <x-slot:gatilho>
                                                <button type="button" class="font-medium text-red-700 hover:underline">Bloquear</button>
                                            </x-slot:gatilho>
                                        </x-ui.confirmation>
                                    @endif
                                @endif
                            @endcan
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-ui.table>

        <div class="mt-4">
            {{ $usuarios->links() }}
        </div>
    @endif
</x-layouts.admin>
