<x-layouts.site :titulo="$pagina->tituloDeMetadados()" :meta-descricao="$pagina->meta_descricao">
    <section class="border-b border-brand-navy/10 bg-brand-paperSoft py-10 lg:py-12">
        <div class="mx-auto max-w-3xl px-5 lg:px-8">
            <h1 class="font-siteDisplay text-3xl font-bold text-brand-navy sm:text-4xl">{{ $pagina->titulo }}</h1>
        </div>
    </section>

    <section class="mx-auto max-w-3xl px-5 py-10 lg:px-8 lg:py-14">
        <div class="prose prose-blue max-w-none">
            {!! $pagina->conteudo !!}
        </div>
    </section>
</x-layouts.site>
