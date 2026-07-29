<x-layouts.site :titulo="$pagina->tituloDeMetadados()" :meta-descricao="$pagina->meta_descricao">
    <section class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900">{{ $pagina->titulo }}</h1>

        <div class="prose mt-6 max-w-none">
            {!! $pagina->conteudo !!}
        </div>
    </section>
</x-layouts.site>
