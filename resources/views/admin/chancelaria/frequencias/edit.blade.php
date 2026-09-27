<x-layouts.admin titulo="Frequência - {{ $evento->titulo }}">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
        <div>
            <a href="{{ route('admin.chancelaria.frequencias.selecionar-evento') }}" class="text-sm font-medium text-blue-800 hover:underline">&larr; Voltar para sessões</a>
            <p class="mt-1 text-sm text-gray-600">
                {{ $evento->tipo->rotulo() }} em {{ $evento->inicio_em->format('d/m/Y H:i') }}
                @if ($evento->local)
                    &middot; {{ $evento->local }}
                @endif
            </p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.chancelaria.frequencias.update', $evento) }}" class="space-y-4" x-data>
        @csrf
        @method('PUT')

        @if ($irmaos->isEmpty())
            <x-ui.empty-state titulo="Nenhum Irmão cadastrado" descricao="Cadastre os Irmãos antes de lançar a frequência.">
                <x-slot:acao>
                    @can('irmaos.criar')
                        <a href="{{ route('admin.irmaos.create') }}"><x-ui.button tipo="button">Cadastrar Irmão</x-ui.button></a>
                    @endcan
                </x-slot:acao>
            </x-ui.empty-state>
        @else
            <div class="flex justify-end">
                <button
                    type="button"
                    @click="$el.closest('form').querySelectorAll('select[name^=frequencias]').forEach(s => s.value = 'presente')"
                    class="text-sm font-semibold text-blue-800 hover:underline"
                >
                    Marcar todos como Presente
                </button>
            </div>

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
        @endif
    </form>
</x-layouts.admin>
