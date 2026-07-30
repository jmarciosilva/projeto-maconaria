<x-layouts.admin titulo="Registrar Frequência">
    @if ($eventos->isEmpty())
        <x-ui.empty-state titulo="Nenhum evento cadastrado" descricao="Cadastre eventos ou sessões antes de registrar frequência." />
    @else
        <x-ui.table :cabecalhos="['Evento', 'Tipo', 'Data', 'Ações']">
            @foreach ($eventos as $evento)
                <tr>
                    <td class="px-4 py-3 text-gray-900">{{ $evento->titulo }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $evento->tipo->rotulo() }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $evento->inicio_em->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-3 text-sm">
                        <a href="{{ route('admin.chancelaria.frequencias.edit', $evento) }}" class="font-medium text-blue-800 hover:underline">Registrar</a>
                    </td>
                </tr>
            @endforeach
        </x-ui.table>
    @endif
</x-layouts.admin>
