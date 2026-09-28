<x-layouts.admin titulo="Editar Notícia">
    <div class="mb-6 flex flex-col gap-4 rounded-lg border border-blue-200 bg-blue-50 p-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-medium text-blue-900">{{ $noticia->titulo }}</p>
            <div class="mt-2 flex flex-wrap items-center gap-2">
                <x-ui.badge :tipo="$noticia->status->value === 'publicada' ? 'sucesso' : 'neutro'">
                    {{ $noticia->status->rotulo() }}
                </x-ui.badge>
                @if ($noticia->publicado_em)
                    <span class="text-xs text-blue-700">
                        Publicado em {{ $noticia->publicado_em->format('d/m/Y') }} às {{ $noticia->publicado_em->format('H:i') }}
                    </span>
                @endif
            </div>
        </div>
        @if ($noticia->status->value === 'publicada')
            <a href="{{ route('noticias.mostrar', $noticia->slug) }}" target="_blank" class="inline-flex">
                <x-ui.button variante="secundario" class="text-sm">
                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                    </svg>
                    Visualizar no site
                </x-ui.button>
            </a>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.noticias.update', $noticia) }}" enctype="multipart/form-data" class="max-w-4xl">
        @csrf
        @method('PUT')

        @include('admin.noticias._form')

        <div class="mt-6 flex gap-3">
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
