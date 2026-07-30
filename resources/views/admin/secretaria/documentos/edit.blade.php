<x-layouts.admin titulo="Editar Documento da Secretaria">
    <div class="mb-4 rounded-md border border-gray-200 bg-white p-4">
        <p class="text-sm text-gray-600">Código</p>
        <p class="text-lg font-semibold text-gray-900">{{ $documento->codigo }}</p>
    </div>

    <form method="POST" action="{{ route('admin.secretaria.documentos.update', $documento) }}" enctype="multipart/form-data" class="max-w-4xl space-y-6">
        @csrf
        @method('PUT')

        @include('admin.secretaria.documentos._form')

        <div class="flex flex-wrap gap-3">
            <x-ui.button tipo="submit" @disabled(in_array($documento->status->value, ['aprovado', 'publicado'], true))>Salvar alterações</x-ui.button>

            <a href="{{ route('admin.secretaria.documentos.index') }}">
                <x-ui.button variante="secundario">Voltar</x-ui.button>
            </a>
        </div>
    </form>

    <div class="mt-4 flex flex-wrap gap-3">
        @can('secretaria.aprovar-ata')
            @if ($documento->podeSerAprovado())
                <form method="POST" action="{{ route('admin.secretaria.documentos.aprovar', $documento) }}">
                    @csrf
                    @method('PATCH')
                    <x-ui.button tipo="submit" variante="secundario">Aprovar</x-ui.button>
                </form>
            @endif
        @endcan

        @can('secretaria.publicar-ata')
            @if ($documento->podeSerPublicado())
                <form method="POST" action="{{ route('admin.secretaria.documentos.publicar', $documento) }}">
                    @csrf
                    @method('PATCH')
                    <x-ui.button tipo="submit">Publicar</x-ui.button>
                </form>
            @endif
        @endcan

        @can('secretaria.editar-ata')
            <x-ui.confirmation :acao="route('admin.secretaria.documentos.destroy', $documento)" metodo="DELETE" titulo="Remover documento" mensagem="Tem certeza que deseja remover este documento da Secretaria?" rotulo="Remover">
                <x-slot:gatilho>
                    <x-ui.button variante="perigo">Remover</x-ui.button>
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
                        <td class="px-4 py-3 text-sm">
                            <a href="{{ route('admin.secretaria.documentos.arquivos.baixar', [$documento, $arquivo]) }}" class="font-medium text-blue-800 hover:underline">Baixar</a>

                            @can('secretaria.editar-ata')
                                <x-ui.confirmation :acao="route('admin.secretaria.documentos.arquivos.destroy', [$documento, $arquivo])" metodo="DELETE" titulo="Remover arquivo" mensagem="Tem certeza que deseja remover este arquivo?" rotulo="Remover">
                                    <x-slot:gatilho>
                                        <button type="button" class="ml-3 font-medium text-red-700 hover:underline">Remover</button>
                                    </x-slot:gatilho>
                                </x-ui.confirmation>
                            @endcan
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
