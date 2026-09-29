<x-layouts.admin titulo="Frequência - {{ $evento->titulo }}">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
        <div>
            <a href="{{ route('admin.chancelaria.frequencias.selecionar-evento') }}" class="text-sm font-medium text-blue-800 hover:underline">&larr; Voltar para sessões</a>
            <p class="mt-1 text-sm text-gray-600">
                {{ $evento->tipo->rotulo() }}
                @if ($evento->sessao_classe)
                    {{ $evento->sessao_classe->rotulo() }}
                @endif
                em {{ $evento->inicio_em->format('d/m/Y H:i') }}
                @if ($evento->local)
                    &middot; {{ $evento->local }}
                @endif
            </p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.chancelaria.frequencias.update', $evento) }}" class="space-y-4"
        x-data="{
            contagem: { presente: 0, ausente: 0, justificado: 0, naoInformado: 0 },
            recontar() {
                const selects = [...$el.querySelectorAll('select[data-frequencia]')];
                this.contagem = {
                    presente: selects.filter(s => s.value === 'presente').length,
                    ausente: selects.filter(s => s.value === 'ausente').length,
                    justificado: selects.filter(s => s.value === 'justificado').length,
                    naoInformado: selects.filter(s => s.value === '').length,
                };
            },
            marcarTodos(valor) {
                $el.querySelectorAll('select[data-frequencia]').forEach(s => s.value = valor);
                this.recontar();
            },
        }"
        x-init="recontar()"
    >
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
            <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-gray-200 bg-white p-4">
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm">
                    <span class="text-gray-700">Presentes: <strong class="text-green-700" x-text="contagem.presente"></strong></span>
                    <span class="text-gray-700">Ausentes: <strong class="text-red-700" x-text="contagem.ausente"></strong></span>
                    <span class="text-gray-700">Justificados: <strong class="text-amber-700" x-text="contagem.justificado"></strong></span>
                    <span class="text-gray-700">Não informados: <strong class="text-gray-500" x-text="contagem.naoInformado"></strong></span>
                </div>
                <div class="flex flex-wrap gap-3">
                    <button type="button" @click="marcarTodos('presente')" class="text-sm font-semibold text-blue-800 hover:underline">
                        Marcar todos como Presente
                    </button>
                    <button type="button" @click="marcarTodos('ausente')" class="text-sm font-semibold text-blue-800 hover:underline">
                        Marcar todos como Ausente
                    </button>
                </div>
            </div>

            <x-ui.table :cabecalhos="['CIM', 'Irmão', 'Status', 'Observação']">
                @foreach ($irmaos as $irmao)
                    @php($frequencia = $frequencias->get($irmao->id))
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $irmao->cim ?: '—' }}</td>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $irmao->nome_completo }}</td>
                        <td class="px-4 py-3">
                            <select name="frequencias[{{ $irmao->id }}][status]" data-frequencia @change="recontar()" class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
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
