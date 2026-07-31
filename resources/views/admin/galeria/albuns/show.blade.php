<x-layouts.admin titulo="Álbum">
    <div class="mb-6 rounded-md border border-gray-200 bg-white p-4 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">{{ $album->titulo }}</h1>
                <p class="mt-1 text-sm text-gray-600">{{ $album->descricao }}</p>
                <p class="mt-2 text-sm text-gray-500">{{ $album->status->rotulo() }} · {{ $album->visibilidade->rotulo() }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @can('galeria.editar')
                    <a href="{{ route('admin.galeria.albuns.edit', $album) }}"><x-ui.button variante="secundario">Editar</x-ui.button></a>
                @endcan

                @can('galeria.excluir')
                    <x-ui.confirmation :acao="route('admin.galeria.albuns.destroy', $album)" metodo="DELETE" titulo="Remover álbum" :mensagem="'Tem certeza que deseja remover o álbum \''.$album->titulo.'\'? As fotografias também serão removidas.'" rotulo="Remover">
                        <x-slot:gatilho>
                            <x-ui.button variante="perigo" tipo="button">Remover</x-ui.button>
                        </x-slot:gatilho>
                    </x-ui.confirmation>
                @endcan
            </div>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($album->fotografias as $foto)
            <figure class="rounded-md border border-gray-200 bg-white p-3 shadow-sm">
                <img src="{{ Storage::url($foto->caminho) }}" alt="{{ $foto->texto_alternativo }}" class="aspect-video w-full rounded object-cover">
                <figcaption class="mt-2 text-sm text-gray-600">{{ $foto->texto_alternativo }}</figcaption>
            </figure>
        @endforeach
    </div>
</x-layouts.admin>
