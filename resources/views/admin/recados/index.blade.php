<x-layouts.admin titulo="Recados do Painel">
    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-gray-600">Gerencie os recados e avisos exibidos no painel inicial dos usuários (Venerável, Secretário, Tesouraria e avisos gerais).</p>
        <a href="{{ route('admin.recados.create') }}"><x-ui.button>Novo recado</x-ui.button></a>
    </div>

    <x-ui.table :cabecalhos="['Categoria', 'Título', 'Situação', 'Publicado em', 'Válido até', 'Ações']">
        @foreach ($recados as $recado)
            <tr>
                <td class="px-4 py-3"><x-ui.badge tipo="neutro">{{ $recado->categoria->rotulo() }}</x-ui.badge></td>
                <td class="px-4 py-3 font-medium text-gray-900">{{ $recado->titulo }}</td>
                <td class="px-4 py-3"><x-ui.badge :tipo="$recado->ativo ? 'sucesso' : 'neutro'">{{ $recado->ativo ? 'Ativo' : 'Inativo' }}</x-ui.badge></td>
                <td class="px-4 py-3 text-gray-600">{{ optional($recado->publicado_em)->format('d/m/Y H:i') ?? '—' }}</td>
                <td class="px-4 py-3 text-gray-600">{{ optional($recado->valido_ate)->format('d/m/Y') ?? '—' }}</td>
                <td class="px-4 py-3">
                    <x-ui.acao-botao :href="route('admin.recados.edit', $recado)" icone="editar" cor="azul">Editar</x-ui.acao-botao>
                </td>
            </tr>
        @endforeach
    </x-ui.table>

    <div class="mt-4">{{ $recados->links() }}</div>
</x-layouts.admin>
