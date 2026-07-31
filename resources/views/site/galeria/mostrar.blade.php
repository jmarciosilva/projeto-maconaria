<x-layouts.site :titulo="$album->titulo" :meta-descricao="$album->descricao">
    <section class="mx-auto max-w-6xl px-5 py-10 lg:px-8 lg:py-14">
        <a href="{{ route('galeria.index') }}" class="inline-flex items-center gap-1.5 text-sm font-bold text-brand-navy hover:underline">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            Voltar para a galeria
        </a>

        <div class="mt-4 flex items-start gap-3 rounded-xl border border-brand-navy/10 bg-white p-5 shadow-sm">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-brand-navy text-base font-bold text-white">
                {{ mb_substr($album->autor->name ?? config('app.name'), 0, 1) }}
            </div>
            <div class="min-w-0 flex-1">
                <h1 class="font-siteDisplay text-2xl font-bold text-brand-navy">{{ $album->titulo }}</h1>
                <p class="mt-1 text-sm text-brand-inkSoft">{{ $album->autor->name ?? 'Administração da Loja' }} · {{ $album->publicado_em?->diffForHumans() }} · {{ $album->fotografias->count() }} fotografia(s)</p>

                @if ($album->descricao)
                    <p class="mt-3 text-brand-ink">{{ $album->descricao }}</p>
                @endif
            </div>
        </div>

        @if ($album->fotografias->isEmpty())
            <div class="mt-8">
                <x-ui.empty-state titulo="Nenhuma fotografia neste álbum" descricao="As fotografias aparecerão aqui assim que forem publicadas." />
            </div>
        @else
            <div class="mt-8 grid gap-3 sm:grid-cols-3 lg:grid-cols-4">
                @foreach ($album->fotografias as $fotografia)
                    <figure class="group overflow-hidden rounded-lg border border-brand-navy/10 bg-white shadow-sm">
                        <div class="overflow-hidden">
                            <img src="{{ Storage::url($fotografia->caminho) }}" alt="{{ $fotografia->texto_alternativo }}" class="aspect-square w-full object-cover transition duration-300 group-hover:scale-105">
                        </div>
                        @if ($fotografia->titulo)
                            <figcaption class="p-2.5 text-sm text-brand-inkSoft">{{ $fotografia->titulo }}</figcaption>
                        @endif
                    </figure>
                @endforeach
            </div>
        @endif
    </section>
</x-layouts.site>
