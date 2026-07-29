<x-layouts.admin :titulo="$irmao->nome_completo">
    <div class="mb-4 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <x-ui.badge :tipo="$irmao->situacao_cadastral->value === 'ativo' ? 'sucesso' : 'neutro'">
                {{ $irmao->situacao_cadastral->rotulo() }}
            </x-ui.badge>

            @if ($irmao->grau_atual)
                <x-ui.badge>{{ $irmao->grau_atual->rotulo() }}</x-ui.badge>
            @endif
        </div>

        @can('irmaos.editar')
            <a href="{{ route('admin.irmaos.edit', $irmao) }}">
                <x-ui.button variante="secundario">Editar</x-ui.button>
            </a>
        @endcan
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-lg bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-sm font-semibold text-gray-900">Dados pessoais</h3>
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div><dt class="text-xs text-gray-500">Nome completo</dt><dd class="text-sm text-gray-900">{{ $irmao->nome_completo }}</dd></div>
                    <div><dt class="text-xs text-gray-500">Nome social</dt><dd class="text-sm text-gray-900">{{ $irmao->nome_social ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-500">CPF</dt><dd class="text-sm text-gray-900">{{ $irmao->cpf }}</dd></div>
                    <div><dt class="text-xs text-gray-500">RG</dt><dd class="text-sm text-gray-900">{{ $irmao->rg ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-500">Data de nascimento</dt><dd class="text-sm text-gray-900">{{ optional($irmao->data_nascimento)->format('d/m/Y') ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-500">E-mail</dt><dd class="text-sm text-gray-900">{{ $irmao->email ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-500">Telefone</dt><dd class="text-sm text-gray-900">{{ $irmao->telefone ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-500">Usuário vinculado</dt><dd class="text-sm text-gray-900">{{ $irmao->usuario?->name ?? 'Nenhum' }}</dd></div>
                </dl>
            </div>

            <div class="rounded-lg bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-sm font-semibold text-gray-900">Endereço</h3>
                <p class="text-sm text-gray-900">
                    @if ($irmao->endereco)
                        {{ $irmao->endereco }}, {{ $irmao->numero ?? 's/n' }}
                        @if ($irmao->complemento) — {{ $irmao->complemento }} @endif
                        <br>{{ $irmao->bairro }} — {{ $irmao->cidade }}/{{ $irmao->estado }}
                        <br>CEP {{ $irmao->cep ?? '—' }}
                    @else
                        Não informado.
                    @endif
                </p>
            </div>

            <div class="rounded-lg bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-sm font-semibold text-gray-900">Dados maçônicos</h3>
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div><dt class="text-xs text-gray-500">CIM / Matrícula</dt><dd class="text-sm text-gray-900">{{ $irmao->cim ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-500">Cargo atual</dt><dd class="text-sm text-gray-900">{{ $irmao->cargo_atual ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-500">Data de iniciação</dt><dd class="text-sm text-gray-900">{{ optional($irmao->data_iniciacao)->format('d/m/Y') ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-500">Data de elevação</dt><dd class="text-sm text-gray-900">{{ optional($irmao->data_elevacao)->format('d/m/Y') ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-500">Data de exaltação</dt><dd class="text-sm text-gray-900">{{ optional($irmao->data_exaltacao)->format('d/m/Y') ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-500">Ingresso na Loja</dt><dd class="text-sm text-gray-900">{{ optional($irmao->data_ingresso_loja)->format('d/m/Y') ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-500">Desligamento</dt><dd class="text-sm text-gray-900">{{ optional($irmao->data_desligamento)->format('d/m/Y') ?? '—' }}</dd></div>
                </dl>
            </div>

            @if ($irmao->observacoes_administrativas)
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="mb-2 text-sm font-semibold text-gray-900">Observações administrativas</h3>
                    <p class="whitespace-pre-line text-sm text-gray-700">{{ $irmao->observacoes_administrativas }}</p>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="rounded-lg bg-white p-6 text-center shadow-sm">
                @if ($irmao->fotografia)
                    <img src="{{ route('admin.irmaos.foto', $irmao) }}" alt="Fotografia de {{ $irmao->nome_completo }}" class="mx-auto h-40 w-40 rounded-full object-cover">
                @else
                    <div class="mx-auto flex h-40 w-40 items-center justify-center rounded-full bg-gray-100 text-gray-400">
                        Sem foto
                    </div>
                @endif
            </div>

            <div class="rounded-lg bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-sm font-semibold text-gray-900">Histórico</h3>

                @if ($irmao->historicos->isEmpty())
                    <p class="text-sm text-gray-500">Nenhum registro no histórico.</p>
                @else
                    <ul class="space-y-4">
                        @foreach ($irmao->historicos as $historico)
                            <li class="border-l-2 border-blue-200 pl-3 text-sm">
                                <p class="font-medium text-gray-900">{{ $historico->tipo->rotulo() }}</p>

                                @if ($historico->valor_anterior || $historico->valor_novo)
                                    <p class="text-gray-600">{{ $historico->valor_anterior ?: '—' }} → {{ $historico->valor_novo ?: '—' }}</p>
                                @endif

                                @if ($historico->observacao)
                                    <p class="text-gray-600">{{ $historico->observacao }}</p>
                                @endif

                                <p class="text-xs text-gray-400">
                                    {{ $historico->data_referencia->format('d/m/Y') }}
                                    @if ($historico->responsavel) por {{ $historico->responsavel->name }} @endif
                                </p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</x-layouts.admin>
