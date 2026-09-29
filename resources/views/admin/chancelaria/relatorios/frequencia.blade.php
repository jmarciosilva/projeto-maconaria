@php
    $configuracaoInstitucional = \App\Models\ConfiguracaoInstitucional::atual();

    // Mesmo asset e mesmo fallback usados pelo site público e pela área
    // restrita: arquivo local, nunca URL externa, para que o navegador o
    // tenha em cache no momento da impressão.
    $brasao = $configuracaoInstitucional->logotipo
        ? asset('storage/'.$configuracaoInstitucional->logotipo)
        : asset('images/logo-loja.png');
@endphp

<x-layouts.admin titulo="Relatório de Frequência">
    <section class="nao-imprimir mb-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-base font-semibold text-gray-900">Relatório de frequência</h2>
        <p class="mt-0.5 text-sm text-gray-500">Informe o período para apurar a frequência dos Irmãos nas sessões registradas.</p>

        <form method="GET" class="mt-4 grid gap-4 sm:grid-cols-4">
            <x-ui.input rotulo="Data inicial" nome="inicio" tipo="date" :valor="request('inicio', $inicio->toDateString())" :erro="$errors->first('inicio')" />
            <x-ui.input rotulo="Data final" nome="fim" tipo="date" :valor="request('fim', $fim->toDateString())" :erro="$errors->first('fim')" />
            <x-ui.select rotulo="Ordenar por" nome="ordenar" :opcoes="['nome' => 'Nome', 'frequencia' => 'Frequência']" :valor="$ordenar" />

            <div class="flex flex-wrap items-center gap-3 sm:col-span-4">
                <x-ui.button tipo="submit">Gerar relatório</x-ui.button>

                @if ($gerou)
                    <button
                        type="button"
                        onclick="window.print()"
                        class="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829V6.75a1.5 1.5 0 0 1 1.5-1.5h7.56a1.5 1.5 0 0 1 1.5 1.5v7.079M6.72 13.829H5.25a1.5 1.5 0 0 0-1.5 1.5v2.25a1.5 1.5 0 0 0 1.5 1.5h1.47m0-5.25h10.56m0 0h1.47a1.5 1.5 0 0 1 1.5 1.5v2.25a1.5 1.5 0 0 1-1.5 1.5h-1.47m-10.56 0v3.421a.75.75 0 0 0 .75.75h9.06a.75.75 0 0 0 .75-.75v-3.421" />
                        </svg>
                        Imprimir / Salvar PDF
                    </button>

                    <span class="text-xs text-gray-500">Impressão em A4 paisagem.</span>
                @endif
            </div>
        </form>
    </section>

    @if (! $gerou)
        <x-ui.empty-state titulo="Selecione um período" descricao="Escolha a data inicial e a data final e clique em Gerar relatório." />
    @else
        <article id="relatorio-frequencia" class="documento rounded-lg border border-gray-200 bg-white p-6 text-gray-900 shadow-sm sm:p-8">
            {{-- Cabeçalho institucional: reutiliza apenas o que já está
                 configurado no sistema (brasão e nome da Loja). --}}
            <header class="documento__cabecalho mb-6 border-b border-gray-300 pb-5 text-center">
                <img src="{{ $brasao }}" alt="Brasão da {{ $configuracaoInstitucional->nome() }}" class="documento__brasao mx-auto mb-3 h-16 w-16 object-contain">

                <p class="documento__loja text-sm font-bold uppercase tracking-wide text-gray-900 sm:text-base">{{ $configuracaoInstitucional->nome() }}</p>
                <p class="documento__orgao mt-0.5 text-xs uppercase tracking-[0.18em] text-gray-600">Chancelaria</p>

                <h2 class="documento__titulo mt-4 text-lg font-bold uppercase tracking-wide text-gray-900 sm:text-xl">Relatório de Frequência</h2>
                <p class="documento__periodo mt-1 text-sm text-gray-700">Período: {{ $inicio->format('d/m/Y') }} a {{ $fim->format('d/m/Y') }}</p>
            </header>

            {{-- Resumo: apenas totais que a apuração já devolve. --}}
            <section class="documento__resumo mb-6 grid gap-px overflow-hidden rounded-md border border-gray-300 bg-gray-300 sm:grid-cols-3">
                <div class="documento__resumo-item bg-white px-4 py-3">
                    <span class="documento__resumo-rotulo block text-[0.7rem] uppercase tracking-wider text-gray-600">Período</span>
                    <span class="documento__resumo-valor block text-sm font-bold text-gray-900">{{ $inicio->format('d/m/Y') }} a {{ $fim->format('d/m/Y') }}</span>
                </div>
                <div class="documento__resumo-item bg-white px-4 py-3">
                    <span class="documento__resumo-rotulo block text-[0.7rem] uppercase tracking-wider text-gray-600">Sessões encontradas no período</span>
                    <span class="documento__resumo-valor block text-sm font-bold text-gray-900">{{ $totalSessoes }}</span>
                </div>
                <div class="documento__resumo-item bg-white px-4 py-3">
                    <span class="documento__resumo-rotulo block text-[0.7rem] uppercase tracking-wider text-gray-600">Irmãos relacionados</span>
                    <span class="documento__resumo-valor block text-sm font-bold text-gray-900">{{ $linhas->count() }}</span>
                </div>
            </section>

            @if ($linhas->isEmpty())
                <p class="text-sm text-gray-600">Nenhum Irmão cadastrado para apurar.</p>
            @else
                <div class="documento__tabela-wrap overflow-x-auto">
                    <table class="documento__tabela min-w-full border-collapse text-sm">
                        <thead>
                            <tr class="border-y border-gray-300 bg-gray-100 text-left text-xs font-semibold uppercase tracking-wide text-gray-700">
                                <th scope="col" class="col-cim px-3 py-2">CIM</th>
                                <th scope="col" class="col-nome px-3 py-2">Irmão</th>
                                <th scope="col" class="col-num px-3 py-2 text-center">
                                    <span class="rotulo-completo">Sessões consideradas</span><span class="rotulo-curto">Consider.</span>
                                </th>
                                <th scope="col" class="col-num px-3 py-2 text-center">
                                    <span class="rotulo-completo">Presenças</span><span class="rotulo-curto">Pres.</span>
                                </th>
                                <th scope="col" class="col-num px-3 py-2 text-center">
                                    <span class="rotulo-completo">Ausências</span><span class="rotulo-curto">Aus.</span>
                                </th>
                                <th scope="col" class="col-num px-3 py-2 text-center">
                                    <span class="rotulo-completo">Justificadas</span><span class="rotulo-curto">Just.</span>
                                </th>
                                <th scope="col" class="col-num px-3 py-2 text-center">
                                    <span class="rotulo-completo">Não informadas</span><span class="rotulo-curto">N/Inf.</span>
                                </th>
                                <th scope="col" class="col-freq px-3 py-2 text-center">
                                    <span class="rotulo-completo">Frequência</span><span class="rotulo-curto">Freq.</span>
                                </th>
                                <th scope="col" class="col-indicador px-3 py-2">Indicador</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($linhas as $linha)
                                <tr>
                                    <td class="px-3 py-2 text-gray-600">{{ $linha['cim'] ?: '—' }}</td>
                                    <td class="documento__celula-nome px-3 py-2 font-medium text-gray-900">{{ $linha['nome'] }}</td>
                                    <td class="px-3 py-2 text-center text-gray-700">{{ $linha['consideradas'] }}</td>
                                    <td class="px-3 py-2 text-center text-gray-700">{{ $linha['presencas'] }}</td>
                                    <td class="px-3 py-2 text-center text-gray-700">{{ $linha['ausencias'] }}</td>
                                    <td class="px-3 py-2 text-center text-gray-700">{{ $linha['justificadas'] }}</td>
                                    <td class="px-3 py-2 text-center text-gray-500">{{ $linha['nao_informadas'] }}</td>
                                    <td class="documento__celula-freq px-3 py-2 text-center font-semibold text-gray-900">
                                        @if ($linha['percentual'] === null)
                                            <span class="documento__sem-dados font-normal text-gray-500" title="Sem sessões consideradas no período">— Sem dados</span>
                                        @else
                                            {{ number_format($linha['percentual'], 1, ',', '.') }}%
                                        @endif
                                    </td>
                                    <td class="px-3 py-2">
                                        @if ($linha['abaixo_do_limite'])
                                            <span class="documento__indicador inline-flex items-center rounded border border-gray-400 bg-gray-50 px-2 py-0.5 text-xs font-semibold text-gray-800">
                                                &#9888; Frequência abaixo de {{ (int) $limiteIndicador }}%
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="documento__rodape-tabela">
                                <td colspan="9" class="px-3 pt-2 text-xs text-gray-500">
                                    &#9888; Indicador para análise da Chancelaria. Não representa decisão sobre a situação do Irmão.
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif

            <section class="documento__criterio mt-6 rounded-md border border-gray-300 p-4 text-xs leading-relaxed text-gray-700">
                <h3 class="documento__criterio-titulo mb-1.5 text-xs font-bold uppercase tracking-widest text-gray-900">Critério deste relatório</h3>
                <p>
                    O percentual de frequência considera apenas as sessões do período em que houve lançamento explícito
                    de frequência para o Irmão. Registros justificados são contabilizados como ausência para efeito do
                    percentual, mas permanecem discriminados em coluna própria. Sessões sem lançamento são apresentadas
                    como &ldquo;Não informadas&rdquo; e não integram o cálculo.
                </p>
                <p class="mt-1.5">
                    Frequência abaixo de {{ (int) $limiteIndicador }}% é apenas um indicador para análise e não representa
                    decisão automática sobre a situação do Irmão.
                </p>
            </section>

            <section class="documento__emissao mt-6 flex flex-wrap justify-between gap-x-10 gap-y-1 text-xs text-gray-700">
                <span>Emitido em: {{ now()->format('d/m/Y') }} às {{ now()->format('H:i') }}</span>
                @if (filled(auth()->user()?->name))
                    <span>Emitido por: {{ auth()->user()->name }}</span>
                @endif
            </section>

            {{-- Linha de assinatura deliberadamente em branco: o sistema sabe
                 quem emitiu o relatório, mas não tem como afirmar quem responde
                 pela Chancelaria, então nada é preenchido automaticamente. --}}
            <section class="documento__assinatura mx-auto mt-12 w-72 text-center">
                <span class="documento__assinatura-linha block border-t border-gray-500"></span>
                <span class="documento__assinatura-rotulo mt-1.5 inline-block text-xs uppercase tracking-widest text-gray-700">Chancelaria</span>
            </section>

            <footer class="documento__rodape mt-8 flex flex-wrap justify-between gap-x-6 gap-y-1 border-t border-gray-300 pt-2 text-[0.7rem] text-gray-500">
                <span>Relatório de Frequência — Chancelaria</span>
                <span>{{ $configuracaoInstitucional->nome() }}</span>
                <span>Emitido em {{ now()->format('d/m/Y') }}</span>
            </footer>
        </article>
    @endif
</x-layouts.admin>
