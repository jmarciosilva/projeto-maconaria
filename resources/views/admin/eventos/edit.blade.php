<x-layouts.admin titulo="Editar Evento">
    <form method="POST" action="{{ route('admin.eventos.update', $evento) }}" enctype="multipart/form-data" class="max-w-4xl space-y-6">
        @csrf
        @method('PUT')

        @include('admin.eventos._form')

        <div class="flex gap-3">
            <x-ui.button tipo="submit">Salvar alterações</x-ui.button>
            <a href="{{ route('admin.eventos.index') }}">
                <x-ui.button variante="secundario">Voltar</x-ui.button>
            </a>
        </div>
    </form>

    @if ($evento->confirmacoesAtivas->isNotEmpty())
        <section class="mt-8 max-w-4xl">
            <h2 class="mb-3 text-base font-semibold text-gray-900">Presenças confirmadas</h2>

            <x-ui.table :cabecalhos="['Usuário', 'E-mail', 'Observação']">
                @foreach ($evento->confirmacoesAtivas as $confirmacao)
                    <tr>
                        <td class="px-4 py-3 text-gray-900">{{ $confirmacao->usuario->name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $confirmacao->usuario->email }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $confirmacao->observacao ?: '—' }}</td>
                    </tr>
                @endforeach
            </x-ui.table>
        </section>
    @endif
</x-layouts.admin>
