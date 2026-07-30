<x-layouts.admin titulo="Publicação do Mural">
    <article class="mb-6 rounded-md border border-gray-200 bg-white p-4 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">{{ $publicacao->titulo }}</h1>
                <p class="mt-2 whitespace-pre-line text-sm text-gray-700">{{ $publicacao->conteudo }}</p>
                <p class="mt-3 text-sm text-gray-500">{{ $publicacao->status->rotulo() }} · {{ $publicacao->visibilidade->rotulo() }}</p>
            </div>
            @can('mural.editar')
                <a href="{{ route('admin.mural.publicacoes.edit', $publicacao) }}"><x-ui.button variante="secundario">Editar</x-ui.button></a>
            @endcan
        </div>
    </article>

    <section class="mb-6 rounded-md border border-gray-200 bg-white p-4 shadow-sm">
        <h2 class="mb-3 text-base font-semibold text-gray-900">Reações</h2>
        <form method="POST" action="{{ route('admin.mural.reacoes.store', $publicacao) }}" class="flex flex-wrap gap-3">
            @csrf
            <x-ui.select rotulo="Tipo" nome="tipo" :opcoes="$tiposReacao->all()" />
            <div class="mt-6"><x-ui.button tipo="submit" variante="secundario">Reagir</x-ui.button></div>
        </form>
        <p class="mt-3 text-sm text-gray-600">{{ $publicacao->reacoes->count() }} reação(ões)</p>
    </section>

    <section class="rounded-md border border-gray-200 bg-white p-4 shadow-sm">
        <h2 class="mb-3 text-base font-semibold text-gray-900">Comentários</h2>
        <div class="mb-4 space-y-3">
            @foreach ($publicacao->comentarios as $comentario)
                <div class="rounded-md bg-gray-50 p-3 text-sm">
                    <p class="font-medium text-gray-900">{{ $comentario->usuario->name ?? 'Usuário removido' }} @unless($comentario->aprovado) <span class="text-amber-700">(pendente)</span> @endunless</p>
                    <p class="mt-1 text-gray-700">{{ $comentario->comentario }}</p>
                    @can('mural.moderar')
                        @unless ($comentario->aprovado)
                            <form method="POST" action="{{ route('admin.mural.comentarios.aprovar', $comentario) }}" class="mt-2">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="font-medium text-blue-800 hover:underline">Aprovar</button>
                            </form>
                        @endunless
                    @endcan
                </div>
            @endforeach
        </div>
        <form method="POST" action="{{ route('admin.mural.comentarios.store', $publicacao) }}" class="space-y-3">
            @csrf
            <textarea name="comentario" rows="3" class="block w-full rounded-md border-gray-300 text-sm shadow-sm" required>{{ old('comentario') }}</textarea>
            <x-ui.button tipo="submit" variante="secundario">Comentar</x-ui.button>
        </form>
    </section>
</x-layouts.admin>
