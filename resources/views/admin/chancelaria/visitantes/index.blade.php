<x-layouts.admin titulo="Visitantes">
    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-gray-600">Registre visitantes vinculados ou não a um evento.</p>
        <a href="{{ route('admin.chancelaria.visitantes.create') }}"><x-ui.button>Novo visitante</x-ui.button></a>
    </div>

    <x-ui.table :cabecalhos="['Nome', 'Loja', 'Potência', 'Evento', 'Ações']">
        @foreach ($visitantes as $visitante)
            <tr>
                <td class="px-4 py-3 font-medium text-gray-900">{{ $visitante->nome }}</td>
                <td class="px-4 py-3 text-gray-600">{{ $visitante->loja_origem ?? '—' }}</td>
                <td class="px-4 py-3 text-gray-600">{{ $visitante->potencia ?? '—' }}</td>
                <td class="px-4 py-3 text-gray-600">{{ $visitante->evento->titulo ?? '—' }}</td>
                <td class="px-4 py-3 text-sm">
                    <a href="{{ route('admin.chancelaria.visitantes.edit', $visitante) }}" class="font-medium text-blue-800 hover:underline">Editar</a>
                </td>
            </tr>
        @endforeach
    </x-ui.table>

    <div class="mt-4">{{ $visitantes->links() }}</div>
</x-layouts.admin>
