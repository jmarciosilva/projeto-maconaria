<x-layouts.admin titulo="Frequência - {{ $evento->titulo }}">
    <form method="POST" action="{{ route('admin.chancelaria.frequencias.update', $evento) }}" class="space-y-4">
        @csrf
        @method('PUT')

        <x-ui.table :cabecalhos="['Irmão', 'Status', 'Observação']">
            @foreach ($irmaos as $irmao)
                @php($frequencia = $frequencias->get($irmao->id))
                <tr>
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $irmao->nome_completo }}</td>
                    <td class="px-4 py-3">
                        <select name="frequencias[{{ $irmao->id }}][status]" class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Não informado</option>
                            @foreach ($statusDisponiveis as $valor => $rotulo)
                                <option value="{{ $valor }}" @selected($frequencia?->status->value === $valor)>{{ $rotulo }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="px-4 py-3">
                        <input name="frequencias[{{ $irmao->id }}][observacao]" value="{{ old("frequencias.$irmao->id.observacao", $frequencia->observacao ?? '') }}" class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </td>
                </tr>
            @endforeach
        </x-ui.table>

        <x-ui.button tipo="submit">Salvar frequência</x-ui.button>
    </form>
</x-layouts.admin>
