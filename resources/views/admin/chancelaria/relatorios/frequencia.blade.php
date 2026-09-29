<x-layouts.admin titulo="Relatório de Frequência">
    {{-- Estilo de impressão local a esta página: o layout admin não expõe stack de styles. --}}
    <style>
        @media print {
            /* Mantém só o relatório: título, período, tabela, nota e responsável. */
            body * { visibility: hidden; }
            #relatorio-frequencia, #relatorio-frequencia * { visibility: visible; }
            #relatorio-frequencia { position: absolute; inset: 0; width: 100%; padding: 0; }
            .nao-imprimir { display: none !important; }
            table { font-size: 11px; }
            thead { display: table-header-group; }
            tr { break-inside: avoid; }
            @page { margin: 12mm; }
        }
    </style>

    <section class="nao-imprimir mb-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-base font-semibold text-gray-900">Relatório de frequência</h2>
        <p class="mt-0.5 text-sm text-gray-500">Informe o período para apurar a frequência dos Irmãos nas sessões registradas.</p>

        <form method="GET" class="mt-4 grid gap-4 sm:grid-cols-4">
            <x-ui.input rotulo="Data inicial" nome="inicio" tipo="date" :valor="request('inicio', $inicio->toDateString())" :erro="$errors->first('inicio')" />
            <x-ui.input rotulo="Data final" nome="fim" tipo="date" :valor="request('fim', $fim->toDateString())" :erro="$errors->first('fim')" />
            <x-ui.select rotulo="Ordenar por" nome="ordenar" :opcoes="['nome' => 'Nome', 'frequencia' => 'Frequência']" :valor="$ordenar" />

            <div class="sm:col-span-4">
                <x-ui.button tipo="submit">Gerar relatório</x-ui.button>
                @if ($gerou)
                    <button type="button" onclick="window.print()" class="ml-3 text-sm font-semibold text-blue-800 hover:underline">Imprimir / Salvar PDF</button>
                @endif
            </div>
        </form>
    </section>

    @if (! $gerou)
        <x-ui.empty-state titulo="Selecione um período" descricao="Escolha a data inicial e a data final e clique em Gerar relatório." />
    @else
        <div id="relatorio-frequencia" class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <header class="mb-5 border-b border-gray-200 pb-4">
                <h1 class="text-lg font-bold text-gray-900">Relatório de Frequência</h1>
                <dl class="mt-2 grid gap-x-8 gap-y-1 text-sm text-gray-700 sm:grid-cols-2">
                    <div><dt class="inline font-medium">Período:</dt> <dd class="inline">{{ $inicio->format('d/m/Y') }} a {{ $fim->format('d/m/Y') }}</dd></div>
                    <div><dt class="inline font-medium">Sessões encontradas no período:</dt> <dd class="inline">{{ $totalSessoes }}</dd></div>
                    <div><dt class="inline font-medium">Gerado em:</dt> <dd class="inline">{{ now()->format('d/m/Y H:i') }}</dd></div>
                    <div><dt class="inline font-medium">Gerado por:</dt> <dd class="inline">{{ auth()->user()?->name }}</dd></div>
                </dl>
            </header>

            @if ($linhas->isEmpty())
                <p class="text-sm text-gray-600">Nenhum Irmão cadastrado para apurar.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                            <tr>
                                <th class="px-3 py-2">CIM</th>
                                <th class="px-3 py-2">Irmão</th>
                                <th class="px-3 py-2 text-right">Sessões consideradas</th>
                                <th class="px-3 py-2 text-right">Presenças</th>
                                <th class="px-3 py-2 text-right">Ausências</th>
                                <th class="px-3 py-2 text-right">Justificadas</th>
                                <th class="px-3 py-2 text-right">Não informadas</th>
                                <th class="px-3 py-2 text-right">Frequência</th>
                                <th class="px-3 py-2">Indicador</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($linhas as $linha)
                                <tr>
                                    <td class="px-3 py-2 text-gray-600">{{ $linha['cim'] ?: '—' }}</td>
                                    <td class="px-3 py-2 font-medium text-gray-900">{{ $linha['nome'] }}</td>
                                    <td class="px-3 py-2 text-right text-gray-700">{{ $linha['consideradas'] }}</td>
                                    <td class="px-3 py-2 text-right text-gray-700">{{ $linha['presencas'] }}</td>
                                    <td class="px-3 py-2 text-right text-gray-700">{{ $linha['ausencias'] }}</td>
                                    <td class="px-3 py-2 text-right text-gray-700">{{ $linha['justificadas'] }}</td>
                                    <td class="px-3 py-2 text-right text-gray-500">{{ $linha['nao_informadas'] }}</td>
                                    <td class="px-3 py-2 text-right font-semibold text-gray-900">
                                        @if ($linha['percentual'] === null)
                                            <span class="font-normal text-gray-500" title="Sem sessões consideradas no período">— Sem dados</span>
                                        @else
                                            {{ number_format($linha['percentual'], 1, ',', '.') }}%
                                        @endif
                                    </td>
                                    <td class="px-3 py-2">
                                        @if ($linha['abaixo_do_limite'])
                                            <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800">
                                                &#9888; Frequência abaixo de {{ (int) $limiteIndicador }}%
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <footer class="mt-6 border-t border-gray-200 pt-4 text-xs leading-relaxed text-gray-600">
                <p>
                    <strong>Critério deste relatório:</strong> o percentual de frequência considera apenas as sessões
                    do período em que houve lançamento explícito de frequência para o Irmão. Registros justificados
                    são contabilizados como ausência para efeito do percentual, mas permanecem discriminados em coluna
                    própria. Sessões sem lançamento são apresentadas como &ldquo;Não informadas&rdquo; e não integram o cálculo.
                </p>
                <p class="mt-2">
                    Frequência abaixo de {{ (int) $limiteIndicador }}% é apenas um indicador para análise e não representa
                    decisão automática sobre a situação do Irmão.
                </p>
            </footer>
        </div>
    @endif
</x-layouts.admin>
