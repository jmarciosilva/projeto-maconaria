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

    {{-- O x-data envolve formulário e modal: a confirmação de "Limpar
         lançamento" tem form próprio e não pode ficar aninhada no
         formulário principal. --}}
    <div
        x-data="{
            contagem: { presente: 0, ausente: 0, justificado: 0, semLancamento: 0 },
            limpar: { aberto: false, nome: '', url: '' },
            recontar() {
                const selects = [...$el.querySelectorAll('select[data-frequencia]')];
                this.contagem = {
                    presente: selects.filter(s => s.value === 'presente').length,
                    ausente: selects.filter(s => s.value === 'ausente').length,
                    justificado: selects.filter(s => s.value === 'justificado').length,
                    semLancamento: selects.filter(s => s.value === '').length,
                };
            },
            marcarPendentes(valor) {
                // Só toca em quem ainda não tem lançamento: uma ação em massa
                // nunca sobrescreve o que o Chanceler já afirmou.
                $el.querySelectorAll('select[data-frequencia]').forEach(s => {
                    if (s.value === '') {
                        s.value = valor;
                        s.dispatchEvent(new Event('change'));
                    }
                });
                this.recontar();
            },
            confirmarLimpeza(nome, url) {
                this.limpar = { aberto: true, nome, url };
            },
        }"
        x-init="recontar()"
    >
        <form method="POST" action="{{ route('admin.chancelaria.frequencias.update', $evento) }}" class="space-y-4">
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
                        <span class="text-gray-700">Sem lançamento: <strong class="text-gray-500" x-text="contagem.semLancamento"></strong></span>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <button type="button" @click="marcarPendentes('presente')" class="text-sm font-semibold text-blue-800 hover:underline">
                            Marcar pendentes como presentes
                        </button>
                        <button type="button" @click="marcarPendentes('ausente')" class="text-sm font-semibold text-blue-800 hover:underline">
                            Marcar pendentes como ausentes
                        </button>
                    </div>
                </div>

                <p class="text-xs text-gray-500">
                    As ações em massa alteram apenas Irmãos sem lançamento. Quem já está como presente, ausente ou
                    justificado permanece como está.
                </p>

                <x-ui.table :cabecalhos="['CIM', 'Irmão', 'Status', 'Motivo / observação', '']">
                    @foreach ($irmaos as $irmao)
                        @php($frequencia = $frequencias->get($irmao->id))
                        <tr x-data="{ status: @js($frequencia?->status->value ?? '') }">
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $irmao->cim ?: '—' }}</td>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $irmao->nome_completo }}</td>
                            <td class="px-4 py-3">
                                <select
                                    name="frequencias[{{ $irmao->id }}][status]"
                                    data-frequencia
                                    x-model="status"
                                    @change="recontar()"
                                    class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                    {{-- A opção vazia só existe enquanto não há lançamento.
                                         Depois de lançado, voltar para vazio não apaga nada:
                                         para remover, use "Limpar lançamento". --}}
                                    @unless ($frequencia)
                                        <option value="">&mdash; sem lançamento &mdash;</option>
                                    @endunless
                                    @foreach ($statusDisponiveis as $valor => $rotulo)
                                        <option value="{{ $valor }}" @selected($frequencia?->status->value === $valor)>{{ $rotulo }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-4 py-3">
                                <label class="sr-only" for="observacao-{{ $irmao->id }}" x-text="status === 'justificado' ? 'Motivo da justificativa' : 'Observação'"></label>
                                <input
                                    id="observacao-{{ $irmao->id }}"
                                    name="frequencias[{{ $irmao->id }}][observacao]"
                                    value="{{ old("frequencias.$irmao->id.observacao", $frequencia?->observacao ?? '') }}"
                                    :placeholder="status === 'justificado' ? 'Motivo da justificativa (ex.: emergência médica, compromisso profissional)' : 'Observação (opcional)'"
                                    :class="status === 'justificado' ? 'border-amber-400 bg-amber-50' : 'border-gray-300'"
                                    class="block w-full rounded-md text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                <p x-show="status === 'justificado'" x-cloak class="mt-1 text-xs text-amber-800">
                                    Informe o motivo: ele será exigido para concluir a frequência desta sessão.
                                </p>
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if ($frequencia)
                                    <button
                                        type="button"
                                        @click="confirmarLimpeza(@js($irmao->nome_completo), @js(route('admin.chancelaria.frequencias.limpar', [$evento, $irmao])))"
                                        class="text-sm font-medium text-gray-600 hover:text-red-700 hover:underline"
                                    >
                                        Limpar lançamento
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </x-ui.table>

                <x-ui.button tipo="submit">Salvar frequência</x-ui.button>
            @endif
        </form>

        {{-- Confirmação de limpeza, fora do formulário principal. --}}
        <div x-show="limpar.aberto" x-cloak class="fixed inset-0 z-50 overflow-y-auto px-4 py-6">
            <div class="fixed inset-0 bg-gray-500/75" @click="limpar.aberto = false"></div>

            <div class="relative mx-auto mt-16 max-w-md rounded-lg bg-white p-6 shadow-xl">
                <h2 class="text-lg font-semibold text-gray-900">Limpar lançamento</h2>
                <p class="mt-2 text-sm text-gray-600">
                    O lançamento de <strong x-text="limpar.nome"></strong> na sessão de
                    {{ $evento->inicio_em->format('d/m/Y') }} será removido, e o Irmão voltará a aparecer
                    como sem lançamento. Esta ação fica registrada na auditoria.
                </p>

                <form method="POST" :action="limpar.url" class="mt-6 flex justify-end gap-3">
                    @csrf
                    @method('DELETE')

                    <x-ui.button variante="secundario" tipo="button" @click="limpar.aberto = false">Cancelar</x-ui.button>
                    <x-ui.button variante="perigo" tipo="submit">Limpar lançamento</x-ui.button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.admin>
