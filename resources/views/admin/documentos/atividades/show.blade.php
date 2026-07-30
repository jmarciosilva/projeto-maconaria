<x-layouts.admin titulo="Atividade">
    <div class="mb-6 rounded-md border border-gray-200 bg-white p-4 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">{{ $atividade->titulo }}</h1>
                <p class="mt-1 text-sm text-gray-600">{{ $atividade->descricao }}</p>
                <p class="mt-3 text-sm text-gray-500">Prazo: {{ $atividade->prazo_entrega_em?->format('d/m/Y H:i') ?? 'sem prazo definido' }}</p>
            </div>
            <x-ui.badge>{{ $atividade->status->rotulo() }}</x-ui.badge>
        </div>

        <div class="mt-4 flex flex-wrap gap-3">
            @can('documentos.avaliar')
                <a href="{{ route('admin.documentos.atividades.edit', $atividade) }}"><x-ui.button variante="secundario">Editar</x-ui.button></a>
            @endcan
            <a href="{{ route('admin.documentos.atividades.index') }}"><x-ui.button variante="secundario">Voltar</x-ui.button></a>
        </div>
    </div>

    @if ($atividade->arquivos->isNotEmpty())
        <section class="mb-8">
            <h2 class="mb-3 text-base font-semibold text-gray-900">Arquivos da atividade</h2>
            <x-ui.table :cabecalhos="['Arquivo', 'Tipo', 'Tamanho', 'Ações']">
                @foreach ($atividade->arquivos as $arquivo)
                    <tr>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $arquivo->nome_original }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $arquivo->mime }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ number_format($arquivo->tamanho / 1024, 1, ',', '.') }} KB</td>
                        <td class="px-4 py-3 text-sm"><a href="{{ route('admin.documentos.arquivos.baixar', $arquivo) }}" class="font-medium text-blue-800 hover:underline">Baixar</a></td>
                    </tr>
                @endforeach
            </x-ui.table>
        </section>
    @endif

    @can('documentos.enviar')
        <section class="mb-8 max-w-4xl rounded-md border border-gray-200 bg-white p-4 shadow-sm">
            <h2 class="mb-3 text-base font-semibold text-gray-900">Enviar trabalho</h2>
            <form method="POST" action="{{ route('admin.documentos.entregas.store', $atividade) }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <x-ui.input rotulo="Título da entrega" nome="titulo" :erro="$errors->first('titulo')" obrigatorio />
                <div>
                    <label for="descricao-entrega" class="block text-sm font-medium text-gray-700">Descrição</label>
                    <textarea id="descricao-entrega" name="descricao" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">{{ old('descricao') }}</textarea>
                </div>
                <div>
                    <label for="arquivos-entrega" class="block text-sm font-medium text-gray-700">Arquivos</label>
                    <input id="arquivos-entrega" name="arquivos[]" type="file" multiple required class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('arquivos.*')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <x-ui.button tipo="submit">Enviar</x-ui.button>
            </form>
        </section>
    @endcan

    <section class="mb-8">
        <h2 class="mb-3 text-base font-semibold text-gray-900">Entregas</h2>
        <x-ui.table :cabecalhos="['Título', 'Usuário', 'Status', 'Arquivos', 'Avaliação']">
            @foreach ($atividade->entregas as $entrega)
                <tr>
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $entrega->titulo }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $entrega->usuario->name ?? 'Usuário removido' }}</td>
                    <td class="px-4 py-3"><x-ui.badge>{{ $entrega->status->rotulo() }}</x-ui.badge></td>
                    <td class="px-4 py-3 text-sm">
                        @foreach ($entrega->arquivos as $arquivo)
                            <a href="{{ route('admin.documentos.arquivos.baixar', $arquivo) }}" class="mr-2 font-medium text-blue-800 hover:underline">{{ $arquivo->nome_original }}</a>
                        @endforeach
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                        @if ($entrega->avaliacao)
                            Nota {{ $entrega->avaliacao->nota ?? 'sem nota' }} — {{ $entrega->avaliacao->parecer }}
                        @else
                            @can('documentos.avaliar')
                                <form method="POST" action="{{ route('admin.documentos.entregas.avaliar', $entrega) }}" class="space-y-2">
                                    @csrf
                                    @method('PATCH')
                                    <x-ui.input rotulo="Nota" nome="nota" tipo="number" />
                                    <textarea name="parecer" rows="2" placeholder="Parecer" class="block w-full rounded-md border-gray-300 text-sm shadow-sm"></textarea>
                                    <x-ui.button tipo="submit" variante="secundario">Avaliar</x-ui.button>
                                </form>
                            @else
                                Pendente
                            @endcan
                        @endif
                    </td>
                </tr>
            @endforeach
        </x-ui.table>
    </section>

    <section class="max-w-4xl rounded-md border border-gray-200 bg-white p-4 shadow-sm">
        <h2 class="mb-3 text-base font-semibold text-gray-900">Comentários</h2>
        <div class="mb-4 space-y-3">
            @forelse ($atividade->comentarios as $comentario)
                <div class="rounded-md bg-gray-50 p-3 text-sm">
                    <p class="font-medium text-gray-900">{{ $comentario->usuario->name ?? 'Usuário removido' }}</p>
                    <p class="mt-1 text-gray-700">{{ $comentario->comentario }}</p>
                </div>
            @empty
                <p class="text-sm text-gray-500">Nenhum comentário registrado.</p>
            @endforelse
        </div>

        <form method="POST" action="{{ route('admin.documentos.comentarios.store', $atividade) }}" class="space-y-3">
            @csrf
            <textarea name="comentario" rows="3" class="block w-full rounded-md border-gray-300 text-sm shadow-sm" required>{{ old('comentario') }}</textarea>
            <x-ui.button tipo="submit" variante="secundario">Comentar</x-ui.button>
        </form>
    </section>
</x-layouts.admin>
