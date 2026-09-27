<x-layouts.restrito titulo="Mural da Loja">
    @php
        $categoriasEmDestaque = [
            \App\Enums\CategoriaRecadoPainel::VENERAVEL,
            \App\Enums\CategoriaRecadoPainel::SECRETARIO,
            \App\Enums\CategoriaRecadoPainel::CHANCELARIA,
            \App\Enums\CategoriaRecadoPainel::TESOURARIA,
        ];

        $coresCard = [
            'roxo' => ['borda' => 'border-t-purple-500', 'icone' => 'bg-purple-100 text-purple-700'],
            'azul' => ['borda' => 'border-t-blue-500', 'icone' => 'bg-blue-100 text-blue-700'],
            'verde' => ['borda' => 'border-t-green-500', 'icone' => 'bg-green-100 text-green-700'],
            'ambar' => ['borda' => 'border-t-amber-500', 'icone' => 'bg-amber-100 text-amber-700'],
            'cinza' => ['borda' => 'border-t-gray-400', 'icone' => 'bg-gray-100 text-gray-700'],
        ];

        $configuracaoInstitucional = \App\Models\ConfiguracaoInstitucional::atual();
        $logotipoSite = $configuracaoInstitucional->logotipo
            ? asset('storage/'.$configuracaoInstitucional->logotipo)
            : asset('images/logo-loja.png');
    @endphp

    <section class="rounded-xl bg-gradient-to-br from-brand-navy to-brand-navyDeep px-6 py-8 text-white sm:px-8">
        <p class="text-xs font-bold uppercase tracking-widest text-brand-sky">Mural da Loja</p>
        <h1 class="mt-2 font-siteDisplay text-2xl font-bold sm:text-3xl">Bem-vindo, {{ auth()->user()->name }}</h1>
        <p class="mt-2 max-w-2xl text-white/75">
            Aqui você acompanha os recados da administração, sua frequência nas sessões e a agenda de próximos encontros da Loja.
        </p>
    </section>

    <section class="mt-6">
        <h2 class="mb-3 font-siteDisplay text-lg font-bold text-brand-navy">Recados da administração</h2>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($categoriasEmDestaque as $categoria)
                @php
                    $recado = $recadosPorCategoria->get($categoria->value, collect())->first();
                    $cor = $coresCard[$categoria->cor()] ?? $coresCard['cinza'];
                    $nomeModal = 'recado-'.$categoria->value;
                @endphp

                <div
                    @if ($recado) @click="$dispatch('open-modal', '{{ $nomeModal }}')" role="button" tabindex="0" @keydown.enter="$dispatch('open-modal', '{{ $nomeModal }}')" @endif
                    class="flex flex-col rounded-lg border-t-4 bg-white p-4 text-left shadow-sm transition {{ $cor['borda'] }} {{ $recado ? 'cursor-pointer hover:-translate-y-0.5 hover:shadow-md' : '' }}"
                >
                    <p class="text-xs font-semibold uppercase tracking-wide text-brand-inkSoft">{{ $categoria->rotulo() }}</p>

                    @if ($recado)
                        <p class="mt-2 text-sm font-bold text-brand-navy">{{ $recado->titulo }}</p>
                        <div class="mt-1 line-clamp-3 flex-1 text-sm leading-relaxed text-brand-inkSoft">{!! $recado->conteudo !!}</div>
                        <span class="mt-3 text-xs font-bold text-brand-navy">Ler recado completo →</span>
                    @else
                        <p class="mt-2 flex-1 text-sm text-brand-inkSoft/70">Nenhum aviso publicado no momento.</p>
                    @endif
                </div>

                @if ($recado)
                    <x-modal :name="$nomeModal">
                        <div class="relative overflow-hidden bg-gradient-to-br from-brand-navy to-brand-navyDeep px-6 py-6 text-white">
                            <div class="pointer-events-none absolute inset-0 flex select-none items-center justify-center opacity-[0.08]" aria-hidden="true">
                                <img src="{{ $logotipoSite }}" alt="" class="h-40 w-40 object-contain">
                            </div>

                            <div class="relative flex items-center gap-4">
                                <img src="{{ $logotipoSite }}" alt="Selo da {{ $configuracaoInstitucional->nome() }}" class="h-14 w-14 shrink-0 rounded-full bg-white/10 object-contain p-1.5 ring-2 ring-white/30">
                                <div class="min-w-0">
                                    <p class="text-xs font-bold uppercase tracking-widest text-brand-sky">{{ $categoria->rotulo() }}</p>
                                    <h3 class="mt-1 font-siteDisplay text-xl font-bold leading-tight">{{ $recado->titulo }}</h3>
                                </div>
                            </div>
                        </div>

                        <div class="p-6">
                            <div class="prose prose-sm max-w-none text-brand-ink">{!! $recado->conteudo !!}</div>
                            @if ($recado->valido_ate)
                                <p class="mt-4 text-xs font-medium text-brand-inkSoft">Válido até {{ $recado->valido_ate->format('d/m/Y') }}</p>
                            @endif
                        </div>

                        <div class="flex justify-end gap-3 border-t border-brand-navy/10 bg-brand-paperSoft px-6 py-4">
                            <x-ui.button variante="secundario" @click="$dispatch('close')">Fechar</x-ui.button>
                        </div>
                    </x-modal>
                @endif
            @endforeach
        </div>
    </section>

    <section class="mt-6 rounded-lg bg-white p-6 shadow-sm">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <h2 class="font-siteDisplay text-lg font-bold text-brand-navy">Próximos eventos e sessões</h2>
            <a href="{{ route('area-restrita.eventos.index') }}" class="text-sm font-bold text-brand-navy hover:underline">Ver agenda →</a>
        </div>

        @if ($proximosEventos->isEmpty())
            <p class="text-sm text-brand-inkSoft">Nenhum evento previsto no momento.</p>
        @else
            <div class="space-y-3">
                @foreach ($proximosEventos as $evento)
                    @php
                        $nomeModalEvento = 'evento-'.$evento->id;
                        $confirmacaoEvento = $evento->confirmacoes->first();
                    @endphp

                    <div
                        @click="$dispatch('open-modal', '{{ $nomeModalEvento }}')"
                        role="button"
                        tabindex="0"
                        @keydown.enter="$dispatch('open-modal', '{{ $nomeModalEvento }}')"
                        class="cursor-pointer rounded-md border border-brand-navy/10 p-4 transition hover:-translate-y-0.5 hover:border-brand-navy/30 hover:bg-brand-paperSoft hover:shadow-md"
                    >
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="text-sm font-bold text-brand-navy">{{ $evento->inicio_em->format('d/m/Y H:i') }} — {{ $evento->titulo }}</p>
                            @if ($confirmacaoEvento?->status->value === 'confirmado')
                                <x-ui.badge tipo="sucesso">Presença confirmada</x-ui.badge>
                            @endif
                        </div>
                        <p class="mt-1 text-xs text-brand-inkSoft">{{ $evento->tipo->rotulo() }} · {{ $evento->visibilidade->rotulo() }}</p>
                    </div>

                    <x-modal :name="$nomeModalEvento">
                        <div class="relative overflow-hidden bg-gradient-to-br from-brand-navy to-brand-navyDeep px-6 py-6 text-white">
                            <div class="pointer-events-none absolute inset-0 flex select-none items-center justify-center opacity-[0.08]" aria-hidden="true">
                                <img src="{{ $logotipoSite }}" alt="" class="h-40 w-40 object-contain">
                            </div>

                            <div class="relative flex items-center gap-4">
                                <img src="{{ $logotipoSite }}" alt="Selo da {{ $configuracaoInstitucional->nome() }}" class="h-14 w-14 shrink-0 rounded-full bg-white/10 object-contain p-1.5 ring-2 ring-white/30">
                                <div class="min-w-0">
                                    <p class="text-xs font-bold uppercase tracking-widest text-brand-sky">{{ $evento->tipo->rotulo() }}</p>
                                    <h3 class="mt-1 font-siteDisplay text-xl font-bold leading-tight">{{ $evento->titulo }}</h3>
                                </div>
                            </div>
                        </div>

                        <div class="p-6">
                            <dl class="grid gap-4 rounded-md bg-brand-paperSoft p-4 sm:grid-cols-2">
                                <div>
                                    <dt class="text-xs font-semibold uppercase text-brand-inkSoft">Início</dt>
                                    <dd class="mt-1 text-sm font-semibold text-brand-navy">{{ $evento->inicio_em->format('d/m/Y H:i') }}</dd>
                                </div>

                                @if ($evento->fim_em)
                                    <div>
                                        <dt class="text-xs font-semibold uppercase text-brand-inkSoft">Término</dt>
                                        <dd class="mt-1 text-sm font-semibold text-brand-navy">{{ $evento->fim_em->format('d/m/Y H:i') }}</dd>
                                    </div>
                                @endif

                                @if ($evento->local)
                                    <div>
                                        <dt class="text-xs font-semibold uppercase text-brand-inkSoft">Local</dt>
                                        <dd class="mt-1 text-sm font-semibold text-brand-navy">{{ $evento->local }}</dd>
                                    </div>
                                @endif

                                @if ($evento->inscricoes_ate)
                                    <div>
                                        <dt class="text-xs font-semibold uppercase text-brand-inkSoft">Confirmações até</dt>
                                        <dd class="mt-1 text-sm font-semibold text-brand-navy">{{ $evento->inscricoes_ate->format('d/m/Y H:i') }}</dd>
                                    </div>
                                @endif
                            </dl>

                            @if ($evento->descricao)
                                <p class="mt-4 whitespace-pre-line text-sm leading-relaxed text-brand-ink">{{ $evento->descricao }}</p>
                            @endif

                            <div class="mt-6 border-t border-brand-navy/10 pt-6">
                                @if ($confirmacaoEvento?->status->value === 'confirmado')
                                    <div class="flex flex-wrap items-center gap-3">
                                        <x-ui.badge tipo="sucesso">Presença confirmada</x-ui.badge>

                                        <form method="POST" action="{{ route('area-restrita.eventos.cancelar-confirmacao', $evento) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-sm font-semibold text-red-700 hover:underline">Cancelar confirmação</button>
                                        </form>
                                    </div>
                                @elseif ($evento->aceitaConfirmacao())
                                    <form method="POST" action="{{ route('area-restrita.eventos.confirmar', $evento) }}" class="space-y-4">
                                        @csrf

                                        <div>
                                            <label for="observacao-{{ $evento->id }}" class="block text-sm font-medium text-brand-inkSoft">Observação (opcional)</label>
                                            <textarea id="observacao-{{ $evento->id }}" name="observacao" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-navy focus:ring-brand-navy sm:text-sm"></textarea>
                                        </div>

                                        <x-ui.button tipo="submit">Confirmar presença</x-ui.button>
                                    </form>
                                @elseif ($evento->permite_confirmacao)
                                    <x-ui.alert tipo="aviso">Este evento não aceita confirmação de presença no momento.</x-ui.alert>
                                @endif
                            </div>
                        </div>

                        <div class="flex flex-col gap-3 border-t border-brand-navy/10 bg-brand-paperSoft px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <a href="{{ route('area-restrita.eventos.mostrar', $evento) }}" class="text-sm font-bold text-brand-navy hover:underline">Ver página completa →</a>
                            <x-ui.button variante="secundario" class="self-start sm:self-auto" @click="$dispatch('close')">Fechar</x-ui.button>
                        </div>
                    </x-modal>
                @endforeach
            </div>
        @endif
    </section>

    <section class="mt-6 rounded-lg bg-white p-6 shadow-sm">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <h2 class="font-siteDisplay text-lg font-bold text-brand-navy">Minha frequência</h2>
            @if ($frequencia)
                <span class="rounded-full bg-brand-paperSoft px-3 py-1 text-sm font-bold text-brand-navy">{{ $frequencia['percentual'] }}% este ano ({{ $frequencia['presentes'] }} de {{ $frequencia['total'] }} sessões)</span>
            @endif
        </div>

        <p class="mb-6 text-xs text-brand-inkSoft/70">Dados ilustrativos para apresentação do novo layout — em breve substituídos pelo histórico real de frequência.</p>

        <div class="grid grid-cols-1 gap-10 lg:grid-cols-2">
            <div>
                <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                    <p class="text-sm font-semibold text-brand-inkSoft">Frequência mensal ({{ now()->format('Y') }})</p>
                    <span class="rounded-full bg-brand-paperSoft px-2.5 py-0.5 text-[0.7rem] font-bold text-brand-navy">média {{ $graficoFrequencia['mediaMensal'] }}%</span>
                </div>
                <x-ui.grafico-area
                    :pontos="collect($graficoFrequencia['mensal'])->map(fn ($p) => ['rotulo' => $p['mes'], 'valor' => $p['percentual']])"
                    cor="#1d2a5c"
                    gradient-id="grad-mensal"
                />
            </div>

            <div>
                <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                    <p class="text-sm font-semibold text-brand-inkSoft">Frequência anual</p>
                    <span class="rounded-full bg-brand-paperSoft px-2.5 py-0.5 text-[0.7rem] font-bold text-brand-navy">média {{ $graficoFrequencia['mediaAnual'] }}%</span>
                </div>
                <x-ui.grafico-area
                    :pontos="collect($graficoFrequencia['anual'])->map(fn ($p) => ['rotulo' => (string) $p['ano'], 'valor' => $p['percentual']])"
                    cor="#a9cfe0"
                    gradient-id="grad-anual"
                />
            </div>
        </div>

        <div class="mt-10 border-t border-brand-navy/10 pt-8">
            <div class="mb-5 flex items-center gap-4 text-xs font-semibold text-brand-inkSoft">
                <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-gradient-to-t from-green-600 to-green-400"></span> Presenças</span>
                <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-gradient-to-t from-red-500 to-red-300"></span> Faltas</span>
            </div>

            <div class="grid grid-cols-1 gap-10 lg:grid-cols-2">
                <div>
                    <p class="mb-4 text-sm font-semibold text-brand-inkSoft">Presenças e faltas por mês</p>
                    <x-ui.grafico-presenca-falta
                        :pontos="collect($graficoFrequencia['mensal'])->map(fn ($p) => ['rotulo' => $p['mes'], 'presencas' => $p['presencas'], 'faltas' => $p['faltas']])"
                        :maximo="$graficoFrequencia['maximoSessoesMes']"
                    />
                </div>

                <div>
                    <p class="mb-4 text-sm font-semibold text-brand-inkSoft">Presenças e faltas por ano</p>
                    <x-ui.grafico-presenca-falta
                        :pontos="collect($graficoFrequencia['anual'])->map(fn ($p) => ['rotulo' => (string) $p['ano'], 'presencas' => $p['presencas'], 'faltas' => $p['faltas']])"
                        :maximo="$graficoFrequencia['maximoSessoesAno']"
                        largura="w-5 sm:w-6"
                    />
                </div>
            </div>
        </div>
    </section>

    <section class="mt-6 rounded-lg bg-white p-6 shadow-sm">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <h2 class="font-siteDisplay text-lg font-bold text-brand-navy">Minha vida financeira</h2>
            <span class="rounded-full bg-brand-paperSoft px-3 py-1 text-sm font-bold text-brand-navy">{{ $financeiro['percentualEmDia'] }}% em dia este ano</span>
        </div>

        <p class="mb-6 text-xs text-brand-inkSoft/70">Dados ilustrativos para apresentação do novo layout — em breve substituídos pelo extrato real da tesouraria.</p>

        <div class="grid grid-cols-1 gap-10 lg:grid-cols-2">
            <div>
                <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                    <p class="text-sm font-semibold text-brand-inkSoft">Extrato de pagamentos ({{ now()->format('Y') }})</p>
                    <span class="rounded-full bg-brand-paperSoft px-2.5 py-0.5 text-[0.7rem] font-bold text-brand-navy">pago R$ {{ number_format($financeiro['totalPago'], 2, ',', '.') }}</span>
                </div>
                <x-ui.grafico-area
                    :pontos="collect($financeiro['extratoMensal'])->map(fn ($p) => ['rotulo' => $p['mes'], 'valor' => $p['pago'], 'exibir' => $p['pago'] > 0 ? 'R$ '.number_format($p['pago'], 0, ',', '.') : 'Em aberto'])"
                    cor="#16a34a"
                    gradient-id="grad-extrato"
                    :maximo="$financeiro['valorMensalidade']"
                />
            </div>

            <div>
                <p class="mb-3 text-sm font-semibold text-brand-inkSoft">Situação de débito</p>

                <div class="mt-1 flex h-8 w-full overflow-hidden rounded-full bg-brand-navy/[0.06] shadow-inner">
                    <div class="flex items-center justify-center bg-gradient-to-r from-green-600 to-green-500 text-xs font-bold text-white" style="width: {{ $financeiro['percentualEmDia'] }}%">
                        @if ($financeiro['percentualEmDia'] >= 15)
                            {{ $financeiro['percentualEmDia'] }}%
                        @endif
                    </div>
                    <div class="flex items-center justify-center bg-gradient-to-r from-red-500 to-red-400 text-xs font-bold text-white" style="width: {{ $financeiro['percentualEmDebito'] }}%">
                        @if ($financeiro['percentualEmDebito'] >= 15)
                            {{ $financeiro['percentualEmDebito'] }}%
                        @endif
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap gap-x-6 gap-y-2 text-sm">
                    <div class="flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-sm bg-green-600"></span>
                        <span class="text-brand-inkSoft">Em dia: <strong class="text-brand-navy">R$ {{ number_format($financeiro['totalPago'], 2, ',', '.') }}</strong></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-sm bg-red-500"></span>
                        <span class="text-brand-inkSoft">Em débito: <strong class="text-brand-navy">R$ {{ number_format($financeiro['totalEmDebito'], 2, ',', '.') }}</strong></span>
                    </div>
                </div>

                @if (count($financeiro['itensEmDebito']) > 0)
                    <div class="mt-4 space-y-2">
                        <p class="text-xs font-semibold uppercase tracking-wide text-brand-inkSoft">Detalhamento do débito</p>

                        @foreach ($financeiro['itensEmDebito'] as $item)
                            <div class="flex items-center justify-between gap-3 rounded-md border border-red-100 bg-red-50 px-3 py-2">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-brand-navy">{{ $item['descricao'] }}</p>
                                    <p class="text-xs text-brand-inkSoft">{{ $item['tipo'] }}</p>
                                </div>
                                <span class="shrink-0 text-sm font-bold text-red-600">R$ {{ number_format($item['valor'], 2, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="mt-4 text-sm font-medium text-green-700">Nenhum débito em aberto.</p>
                @endif
            </div>
        </div>
    </section>

    @if ($recadosGerais->isNotEmpty())
        <section class="mt-6 rounded-lg bg-white p-6 shadow-sm">
            <h2 class="mb-4 font-siteDisplay text-lg font-bold text-brand-navy">Avisos gerais</h2>

            <div class="space-y-4">
                @foreach ($recadosGerais as $recado)
                    <div class="rounded-md border border-brand-navy/10 p-4">
                        <p class="text-sm font-bold text-brand-navy">{{ $recado->titulo }}</p>
                        <div class="mt-1 text-sm leading-relaxed text-brand-inkSoft">{!! $recado->conteudo !!}</div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</x-layouts.restrito>
