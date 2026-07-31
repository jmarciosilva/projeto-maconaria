<x-layouts.site titulo="Calendário" meta-descricao="Calendário público de eventos e sessões da Loja.">
    <section class="border-b border-brand-navy/10 bg-brand-paperSoft py-10 lg:py-12">
        <div class="mx-auto flex max-w-6xl flex-wrap items-end justify-between gap-4 px-5 lg:px-8">
            <div>
                <h1 class="font-siteDisplay text-3xl font-bold text-brand-navy sm:text-4xl">Calendário</h1>
                <p class="mt-2 text-lg text-brand-inkSoft">Eventos e sessões entre {{ $inicio->format('d/m/Y') }} e {{ $fim->format('d/m/Y') }}.</p>
            </div>

            <a href="{{ route('eventos.index') }}" class="border-b-2 border-brand-skyDeep font-bold text-brand-navy hover:border-brand-navy">Ver lista de eventos →</a>
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-5 py-10 lg:px-8 lg:py-14">
        @if ($eventos->isEmpty())
            <x-ui.empty-state titulo="Nenhum evento público no período" descricao="Novos eventos aparecerão aqui." />
        @else
            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($eventos as $dia => $eventosDoDia)
                    <section class="rounded-lg border border-brand-navy/10 bg-white p-5 shadow-sm">
                        <h2 class="font-siteDisplay text-lg font-bold text-brand-navy">{{ \Illuminate\Support\Carbon::parse($dia)->translatedFormat('d \d\e F, l') }}</h2>

                        <div class="mt-4 flex flex-col gap-3">
                            @foreach ($eventosDoDia as $evento)
                                <a href="{{ route('eventos.mostrar', $evento->slug) }}" class="block rounded-md border border-brand-navy/10 p-3.5 transition hover:border-brand-skyDeep hover:bg-brand-paperSoft">
                                    <p class="text-sm font-bold text-brand-ink">{{ $evento->inicio_em->format('H:i') }} — {{ $evento->titulo }}</p>
                                    <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-brand-inkSoft">{{ $evento->tipo->rotulo() }}</p>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        @endif
    </section>
</x-layouts.site>
