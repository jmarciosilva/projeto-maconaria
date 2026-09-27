@props(['pontos', 'maximo', 'largura' => 'w-4 sm:w-5'])

@php
    // Com muitos pontos (ex.: 12 meses), as colunas ficam apertadas demais na
    // largura de um celular — abaixo desse mínimo, preferimos rolagem
    // horizontal a números/barras espremidos.
    $larguraMinima = max(count($pontos) * 38, 220);
@endphp

<div
    x-data="{
        podeRolar: false,
        noInicio: true,
        noFim: true,
        atualizarRolagem() {
            const el = $refs.rolagem;
            this.podeRolar = el.scrollWidth > el.clientWidth + 1;
            this.noInicio = el.scrollLeft <= 1;
            this.noFim = el.scrollLeft + el.clientWidth >= el.scrollWidth - 1;
        }
    }"
    x-init="$nextTick(() => atualizarRolagem())"
    @resize.window="atualizarRolagem()"
    class="relative min-w-0"
>
    <div x-show="podeRolar && !noInicio" x-cloak class="pointer-events-none absolute inset-y-0 left-0 z-10 w-6 bg-gradient-to-r from-white to-transparent"></div>
    <div x-show="podeRolar && !noFim" x-cloak class="pointer-events-none absolute inset-y-0 right-0 z-10 w-6 bg-gradient-to-l from-white to-transparent"></div>

    <div x-ref="rolagem" @scroll="atualizarRolagem()" class="-mx-1 overflow-x-auto px-1">
        <div class="flex items-end gap-1.5 sm:gap-2.5" style="min-width: {{ $larguraMinima }}px">
            @foreach ($pontos as $ponto)
                @php
                    $percentualPresenca = $maximo > 0 ? $ponto['presencas'] / $maximo * 100 : 0;
                    $percentualFalta = $maximo > 0 ? $ponto['faltas'] / $maximo * 100 : 0;
                @endphp

                <div class="flex flex-1 flex-col items-center gap-1.5">
                    <div class="flex items-center gap-1 text-[0.65rem] font-bold leading-none">
                        <span class="text-green-700">{{ $ponto['presencas'] }}</span>
                        <span class="text-brand-inkSoft/30">/</span>
                        <span class="text-red-500">{{ $ponto['faltas'] }}</span>
                    </div>

                    <div class="relative h-24 {{ $largura }} overflow-hidden rounded-md bg-brand-navy/[0.06] shadow-inner" title="{{ $ponto['rotulo'] }}: {{ $ponto['presencas'] }} presenças, {{ $ponto['faltas'] }} faltas">
                        <div class="absolute inset-x-0 bottom-0 rounded-t-sm bg-gradient-to-t from-green-600 to-green-400" style="height: {{ $percentualPresenca }}%"></div>
                        <div class="absolute inset-x-0 rounded-t-sm bg-gradient-to-t from-red-500 to-red-300" style="bottom: {{ $percentualPresenca }}%; height: {{ $percentualFalta }}%"></div>
                    </div>

                    <span class="text-[0.65rem] text-brand-inkSoft">{{ $ponto['rotulo'] }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>
