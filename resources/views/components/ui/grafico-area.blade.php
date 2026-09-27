@props(['pontos', 'cor' => '#1d2a5c', 'gradientId', 'maximo' => 100])

@php
    $largura = 600;
    $altura = 130;
    $topoPad = 16;
    $baseY = $altura - 4;
    $n = count($pontos);
    $passoX = $n > 1 ? ($largura - 20) / ($n - 1) : 0;
    $maximo = $maximo > 0 ? $maximo : 1;

    $coordenadas = collect($pontos)->values()->map(function (array $ponto, int $i) use ($passoX, $topoPad, $baseY, $maximo) {
        return [
            'x' => round(10 + $i * $passoX, 1),
            'y' => round($topoPad + (1 - $ponto['valor'] / $maximo) * ($baseY - $topoPad), 1),
            'valor' => $ponto['valor'],
            'rotulo' => $ponto['rotulo'],
            'exibir' => $ponto['exibir'] ?? ($ponto['valor'].'%'),
        ];
    });

    $pontosLinha = $coordenadas->map(fn (array $c) => $c['x'].','.$c['y'])->implode(' ');
    $primeiraX = $coordenadas->first()['x'];
    $ultimaX = $coordenadas->last()['x'];
    $areaPath = 'M '.$primeiraX.','.$baseY.' L '.$pontosLinha.' L '.$ultimaX.','.$baseY.' Z';

    // Com muitos pontos (ex.: 12 meses), o gráfico fica ilegível espremido na
    // largura de um celular — abaixo desse mínimo, preferimos permitir rolagem
    // horizontal a distorcer os rótulos.
    $larguraMinima = max($n * 46, 260);
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
        <div style="min-width: {{ $larguraMinima }}px">
            <svg viewBox="0 0 {{ $largura }} {{ $altura }}" preserveAspectRatio="none" class="h-32 w-full overflow-visible sm:h-36">
                <defs>
                    <linearGradient id="{{ $gradientId }}" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="{{ $cor }}" stop-opacity="0.35" />
                        <stop offset="100%" stop-color="{{ $cor }}" stop-opacity="0" />
                    </linearGradient>
                </defs>

                @foreach ([0, 25, 50, 75, 100] as $marca)
                    @php($yGrade = round($topoPad + (100 - $marca) / 100 * ($baseY - $topoPad), 1))
                    <line x1="0" y1="{{ $yGrade }}" x2="{{ $largura }}" y2="{{ $yGrade }}" stroke="#1d2a5c" stroke-opacity="0.08" stroke-width="1" />
                @endforeach

                <path d="{{ $areaPath }}" fill="url(#{{ $gradientId }})" />

                <polyline points="{{ $pontosLinha }}" fill="none" stroke="{{ $cor }}" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke" />

                @foreach ($coordenadas as $c)
                    <circle cx="{{ $c['x'] }}" cy="{{ $c['y'] }}" r="4.5" fill="white" stroke="{{ $cor }}" stroke-width="2.5" vector-effect="non-scaling-stroke">
                        <title>{{ $c['rotulo'] }}: {{ $c['exibir'] }}</title>
                    </circle>
                @endforeach
            </svg>

            <div class="mt-2 flex justify-between text-[0.7rem] text-brand-inkSoft">
                @foreach ($coordenadas as $c)
                    <div class="flex-1 text-center">
                        <span class="block text-[0.75rem] font-bold text-brand-navy">{{ $c['exibir'] }}</span>
                        <span>{{ $c['rotulo'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
