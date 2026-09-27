<x-layouts.admin titulo="Registrar Frequência">
    @can('chancelaria.criar')
        <section class="mb-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-base font-semibold text-gray-900">Registrar sessão passada</h2>
            <p class="mt-0.5 text-sm text-gray-500">Cadastre rapidamente uma sessão já realizada, só para lançar a presença dos Irmãos nela. Para eventos da agenda pública do site, use o módulo Eventos.</p>

            <form method="POST" action="{{ route('admin.chancelaria.frequencias.armazenar-sessao') }}" class="mt-4 grid gap-4 sm:grid-cols-3">
                @csrf
                <x-ui.input rotulo="Título (opcional)" nome="titulo" :erro="$errors->first('titulo')" placeholder="Ex.: Sessão ordinária" />
                <x-ui.input rotulo="Data da sessão" nome="inicio_em" tipo="datetime-local" :erro="$errors->first('inicio_em')" obrigatorio />
                <x-ui.input rotulo="Local (opcional)" nome="local" :erro="$errors->first('local')" />

                <div class="sm:col-span-3">
                    <x-ui.button tipo="submit">Registrar sessão e lançar presença</x-ui.button>
                </div>
            </form>
        </section>
    @endcan

    @if ($eventos->isEmpty())
        <x-ui.empty-state titulo="Nenhum evento cadastrado" descricao="Registre uma sessão acima para começar a lançar frequência." />
    @else
        <x-ui.table :cabecalhos="['Evento', 'Tipo', 'Data', 'Ações']">
            @foreach ($eventos as $evento)
                <tr>
                    <td class="px-4 py-3 text-gray-900">{{ $evento->titulo }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $evento->tipo->rotulo() }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $evento->inicio_em->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-3 text-sm">
                        <a href="{{ route('admin.chancelaria.frequencias.edit', $evento) }}" class="font-medium text-blue-800 hover:underline">Registrar</a>
                    </td>
                </tr>
            @endforeach
        </x-ui.table>
    @endif
</x-layouts.admin>
