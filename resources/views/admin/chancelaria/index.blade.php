<x-layouts.admin titulo="Chancelaria">
    <div class="mb-6 grid gap-4 md:grid-cols-3">
        <div class="rounded-md border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-sm text-gray-600">Presenças</p>
            <p class="mt-1 text-2xl font-bold text-green-700">{{ $presentes }}</p>
        </div>
        <div class="rounded-md border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-sm text-gray-600">Ausências</p>
            <p class="mt-1 text-2xl font-bold text-red-700">{{ $ausentes }}</p>
        </div>
        <div class="rounded-md border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-sm text-gray-600">Justificativas</p>
            <p class="mt-1 text-2xl font-bold text-amber-700">{{ $justificados }}</p>
        </div>
    </div>

    <div class="mb-6 flex flex-wrap gap-3">
        <a href="{{ route('admin.chancelaria.frequencias.selecionar-evento') }}"><x-ui.button>Registrar frequência</x-ui.button></a>
        <a href="{{ route('admin.chancelaria.visitantes.index') }}"><x-ui.button variante="secundario">Visitantes</x-ui.button></a>
        <a href="{{ route('admin.chancelaria.comunicados.index') }}"><x-ui.button variante="secundario">Comunicados</x-ui.button></a>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <section>
            <h2 class="mb-3 text-base font-semibold text-gray-900">Eventos recentes</h2>
            <x-ui.table :cabecalhos="['Evento', 'Data', 'Confirmações']">
                @foreach ($eventosRecentes as $evento)
                    <tr>
                        <td class="px-4 py-3 text-gray-900">{{ $evento->titulo }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $evento->inicio_em->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $evento->confirmacoes_ativas_count }}</td>
                    </tr>
                @endforeach
            </x-ui.table>
        </section>

        <section>
            <h2 class="mb-3 text-base font-semibold text-gray-900">Visitantes recentes</h2>
            <x-ui.table :cabecalhos="['Nome', 'Loja', 'Evento']">
                @foreach ($visitantesRecentes as $visitante)
                    <tr>
                        <td class="px-4 py-3 text-gray-900">{{ $visitante->nome }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $visitante->loja_origem ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $visitante->evento->titulo ?? '—' }}</td>
                    </tr>
                @endforeach
            </x-ui.table>
        </section>
    </div>
</x-layouts.admin>
