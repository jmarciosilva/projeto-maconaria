<x-layouts.admin titulo="Editar Notícia">
    <form method="POST" action="{{ route('admin.noticias.update', $noticia) }}" class="max-w-4xl space-y-6">
        @csrf
        @method('PUT')

        @include('admin.noticias._form')

        <div class="flex gap-3">
            <x-ui.button tipo="submit">Salvar alterações</x-ui.button>
            <a href="{{ route('admin.noticias.index') }}">
                <x-ui.button variante="secundario">Voltar</x-ui.button>
            </a>
        </div>
    </form>

    @if ($noticia->versoes->isNotEmpty())
        <section class="mt-8 max-w-4xl">
            <h2 class="mb-3 text-base font-semibold text-gray-900">Histórico de versões</h2>

            <x-ui.table :cabecalhos="['Versão', 'Status', 'Usuário', 'Data']">
                @foreach ($noticia->versoes()->with('usuario')->latest('versao')->get() as $versao)
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
