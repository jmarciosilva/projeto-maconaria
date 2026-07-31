<x-layouts.admin titulo="Editar Documento da Secretaria">
    <div class="mb-6 flex items-center gap-3 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gray-100 text-gray-600">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6-10.125a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0Zm1.294 6.336a6.721 6.721 0 0 1-3.17.789 6.721 6.721 0 0 1-3.168-.789 3.376 3.376 0 0 1 6.338 0Z" />
            </svg>
        </span>
        <div>
            <p class="text-sm text-gray-600">Código</p>
            <p class="text-lg font-semibold text-gray-900">{{ $documento->codigo }}</p>
        </div>
    </div>

    @php
        $documentoBloqueado = in_array($documento->status->value, ['aprovado', 'publicado'], true);
    @endphp

    <form method="POST" action="{{ route('admin.secretaria.documentos.update', $documento) }}" enctype="multipart/form-data" class="max-w-4xl">
        @csrf
        @method('PUT')

        @include('admin.secretaria.documentos._form')

        <div class="mt-6 flex flex-wrap gap-3">
            <x-ui.button tipo="submit" :disabled="$documentoBloqueado">Salvar alterações</x-ui.button>

            <a href="{{ route('admin.secretaria.documentos.index') }}">
                <x-ui.button variante="secundario">Voltar</x-ui.button>
            </a>
        </div>
    </form>

    <div class="mt-4 flex flex-wrap items-center gap-2">
        @can('secretaria.aprovar-ata')
            @if ($documento->podeSerAprovado())
                <form method="POST" action="{{ route('admin.secretaria.documentos.aprovar', $documento) }}">
                    @csrf
                    @method('PATCH')
                    <x-ui.acao-botao icone="aprovar" cor="verde" tipo="submit">Aprovar</x-ui.acao-botao>
                </form>
            @endif
        @endcan

        @can('secretaria.publicar-ata')
            @if ($documento->podeSerPublicado())
                <form method="POST" action="{{ route('admin.secretaria.documentos.publicar', $documento) }}">
                    @csrf
                    @method('PATCH')
                    <x-ui.acao-botao icone="publicar" cor="verde" tipo="submit">Publicar</x-ui.acao-botao>
                </form>
            @endif
        @endcan

        @can('secretaria.editar-ata')
            <x-ui.confirmation :acao="route('admin.secretaria.documentos.destroy', $documento)" metodo="DELETE" titulo="Remover documento" mensagem="Tem certeza que deseja remover este documento da Secretaria?" rotulo="Remover">
                <x-slot:gatilho>
                    <x-ui.acao-botao icone="remover" cor="vermelho" tipo="button">Remover</x-ui.acao-botao>
                </x-slot:gatilho>
            </x-ui.confirmation>
        @endcan
    </div>

    @if ($documento->arquivos->isNotEmpty())
        <section class="mt-8 max-w-4xl">
            <h2 class="mb-3 text-base font-semibold text-gray-900">Arquivos anexados</h2>

            <x-ui.table :cabecalhos="['Arquivo', 'Tipo', 'Tamanho', 'Ações']">
                @foreach ($documento->arquivos as $arquivo)
                    <tr>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $arquivo->nome_original }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $arquivo->mime }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ number_format($arquivo->tamanho / 1024, 1, ',', '.') }} KB</td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <x-ui.acao-botao :href="route('admin.secretaria.documentos.arquivos.baixar', [$documento, $arquivo])" icone="baixar" cor="verde">Baixar</x-ui.acao-botao>

                                @can('secretaria.editar-ata')
                                    <x-ui.confirmation :acao="route('admin.secretaria.documentos.arquivos.destroy', [$documento, $arquivo])" metodo="DELETE" titulo="Remover arquivo" mensagem="Tem certeza que deseja remover este arquivo?" rotulo="Remover">
                                        <x-slot:gatilho>
                                            <x-ui.acao-botao icone="remover" cor="vermelho" tipo="button">Remover</x-ui.acao-botao>
                                        </x-slot:gatilho>
                                    </x-ui.confirmation>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>
        </section>
    @endif

    @if ($documento->versoes->isNotEmpty())
        <section class="mt-8 max-w-4xl">
            <h2 class="mb-3 text-base font-semibold text-gray-900">Histórico de versões</h2>

            <x-ui.table :cabecalhos="['Versão', 'Status', 'Usuário', 'Data']">
                @foreach ($documento->versoes->sortByDesc('versao') as $versao)
                    <tr>
                        <td class="px-4 py-3 text-gray-900">#{{ $versao->versao }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $versao->status->rotulo() }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $versao->usuario->name ?? 'Sistema' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $versao->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                @endforeach
            </x-ui.table>
        </section>
    @endif
</x-layouts.admin>
