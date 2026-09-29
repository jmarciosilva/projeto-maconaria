{{--
    View exclusiva do PDF. Não estende o layout administrativo e não usa
    Tailwind, Alpine ou JavaScript: o DomPDF não interpreta flexbox, grid,
    variáveis CSS nem @media, então todo o layout aqui é feito com tabelas,
    bordas e padding.

    Os dados chegam prontos do ChancelariaRelatorioController, que usa a
    mesma apuração da tela (CalculadoraFrequencia). Nenhuma fórmula é
    recalculada nesta view.
--}}
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Relatório de Frequência — {{ $configuracaoInstitucional->nome() }}</title>
    <style>
        @page {
            margin: 14mm 15mm 16mm;
        }

        body {
            color: #000;
            font-family: "DejaVu Sans", sans-serif;
            font-size: 9pt;
            margin: 0;
            padding: 0;
        }

        /* ---------- cabeçalho institucional ---------- */

        .cabecalho {
            border-bottom: 1pt solid #1d2a5c;
            padding-bottom: 3mm;
            text-align: center;
        }

        /* O DomPDF respeita height em imagens com precisão (medido: 17mm
           pedidos = 17,0mm no papel), então o cabeçalho tem altura estável
           mesmo que o administrador envie um logotipo grande. O max-width
           cobre o caso extremo de um logotipo muito alongado. */
        .cabecalho img {
            height: 17mm;
            max-width: 60mm;
            width: auto;
        }

        .loja {
            font-size: 13pt;
            font-weight: bold;
            margin: 2mm 0 0;
            text-transform: uppercase;
        }

        .orgao {
            font-size: 9pt;
            letter-spacing: 2pt;
            margin: 1mm 0 0;
            text-transform: uppercase;
        }

        .titulo {
            font-size: 15pt;
            font-weight: bold;
            letter-spacing: 1pt;
            margin: 3mm 0 0;
            text-transform: uppercase;
        }

        .periodo {
            font-size: 10pt;
            margin: 1.5mm 0 0;
        }

        /* ---------- resumo ---------- */

        .resumo {
            border-collapse: collapse;
            margin-top: 5mm;
            width: 100%;
        }

        .resumo td {
            border: 0.5pt solid #9aa3b2;
            padding: 2.5mm 4mm;
            width: 33.33%;
        }

        .resumo-rotulo {
            display: block;
            font-size: 7.5pt;
            letter-spacing: 0.6pt;
            text-transform: uppercase;
        }

        .resumo-valor {
            display: block;
            font-size: 10.5pt;
            font-weight: bold;
            padding-top: 0.8mm;
        }

        /* ---------- tabela ---------- */

        .tabela {
            border-collapse: collapse;
            font-size: 8pt;
            margin-top: 5mm;
            table-layout: fixed;
            width: 100%;
        }

        .tabela th,
        .tabela td {
            border: 0.4pt solid #8d95a3;
            padding: 1.6mm 2mm;
            vertical-align: top;
            word-wrap: break-word;
        }

        .tabela thead th {
            background-color: #e8ecf2;
            /* 7pt é o mínimo confortável: os rótulos por extenso cabem nas
               larguras abaixo sem apertar a leitura. */
            font-size: 7pt;
            font-weight: bold;
            text-align: left;
            text-transform: uppercase;
            /* Normal, e não break-word: um rótulo só pode quebrar no espaço
               ("SEM / REGISTRO"), nunca no meio da palavra. */
            word-wrap: normal;
        }

        .tabela tr {
            page-break-inside: avoid;
        }

        /* Larguras somam 100%, na ordem de prioridade acordada: nome do Irmão
           primeiro, depois Observação e os rótulos longos. */
        .col-cim { width: 7%; }
        .col-nome { width: 25%; }
        .col-sessoes { text-align: center; width: 8%; }
        .col-presencas { text-align: center; width: 8.5%; }
        .col-ausencias { text-align: center; width: 8.5%; }
        .col-justificadas { text-align: center; width: 10%; }
        .col-sem-registro { text-align: center; width: 10%; }
        .col-freq { text-align: center; width: 9%; }
        .col-observacao { width: 14%; }

        .celula-nome { font-weight: bold; }
        .celula-num { text-align: center; }
        .celula-freq { font-weight: bold; text-align: center; }
        .sem-dados { color: #555; font-weight: normal; }

        /* O contorno e o símbolo carregam a informação: o documento continua
           compreensível impresso em preto e branco. */
        .indicador {
            border: 0.5pt solid #000;
            font-size: 7pt;
            font-weight: bold;
            padding: 0.4mm 1.2mm;
        }

        .nota-tabela {
            font-size: 7.5pt;
            padding-top: 1.5mm;
        }

        /* ---------- critério, emissão, assinatura ---------- */

        .criterio {
            border: 0.5pt solid #9aa3b2;
            font-size: 8pt;
            line-height: 1.45;
            margin-top: 4mm;
            padding: 2.5mm 4mm;
            page-break-inside: avoid;
        }

        .criterio-titulo {
            font-size: 8pt;
            font-weight: bold;
            letter-spacing: 1pt;
            margin: 0 0 1.5mm;
            text-transform: uppercase;
        }

        .criterio p {
            margin: 0 0 1.5mm;
        }

        .criterio p.ultimo {
            margin: 1.5mm 0 0;
        }

        .verbetes {
            border-collapse: collapse;
            width: 100%;
        }

        .verbetes td {
            padding: 0 4mm 1.2mm 0;
            vertical-align: top;
            width: 50%;
        }

        /* Emissão e assinatura dividem a mesma linha: além de ser o arranjo
           usual de documento oficial, economiza a altura de um bloco inteiro
           e evita que a assinatura escorra sozinha para a página seguinte.
           Tabela, e não div, porque o page-break-inside: avoid do DomPDF é
           confiável em <tr> mas não em bloco solto. */
        .fecho {
            font-size: 8.5pt;
            margin-top: 6mm;
            page-break-inside: avoid;
            width: 100%;
        }

        .fecho td {
            padding: 0;
            vertical-align: bottom;
        }

        .fecho-emissao {
            line-height: 1.6;
            width: 55%;
        }

        .fecho-assinatura {
            text-align: center;
            width: 45%;
        }

        .assinatura-linha {
            border-top: 0.5pt solid #000;
            margin: 0 auto;
            width: 80mm;
        }

        .assinatura-rotulo {
            font-size: 8.5pt;
            letter-spacing: 1pt;
            padding-top: 1.5mm;
            text-transform: uppercase;
        }

    </style>
</head>
<body>
    <div class="cabecalho">
        @if ($brasao)
            <img src="{{ $brasao }}" alt="">
        @endif

        <div class="loja">{{ $configuracaoInstitucional->nome() }}</div>
        <div class="orgao">Chancelaria</div>
        <div class="titulo">Relatório de Frequência</div>
        <div class="periodo">Período: {{ $inicio->format('d/m/Y') }} a {{ $fim->format('d/m/Y') }}</div>
    </div>

    <table class="resumo">
        <tr>
            <td>
                <span class="resumo-rotulo">Período</span>
                <span class="resumo-valor">{{ $inicio->format('d/m/Y') }} a {{ $fim->format('d/m/Y') }}</span>
            </td>
            <td>
                <span class="resumo-rotulo">Sessões encontradas no período</span>
                <span class="resumo-valor">{{ $totalSessoes }}</span>
            </td>
            <td>
                <span class="resumo-rotulo">Irmãos relacionados</span>
                <span class="resumo-valor">{{ $linhas->count() }}</span>
            </td>
        </tr>
    </table>

    @if ($linhas->isEmpty())
        <p style="font-size: 9pt; margin-top: 5mm;">Nenhum Irmão cadastrado para apurar.</p>
    @else
        <table class="tabela">
            <thead>
                <tr>
                    <th class="col-cim">CIM</th>
                    <th class="col-nome">Irmão</th>
                    <th class="col-sessoes">Sessões</th>
                    <th class="col-presencas">Presenças</th>
                    <th class="col-ausencias">Ausências</th>
                    <th class="col-justificadas">Justificadas</th>
                    <th class="col-sem-registro">Sem registro</th>
                    <th class="col-freq">Frequência</th>
                    <th class="col-observacao">Observação</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($linhas as $linha)
                    <tr>
                        <td>{{ $linha['cim'] ?: '—' }}</td>
                        <td class="celula-nome">{{ $linha['nome'] }}</td>
                        <td class="celula-num">{{ $linha['consideradas'] }}</td>
                        <td class="celula-num">{{ $linha['presencas'] }}</td>
                        <td class="celula-num">{{ $linha['ausencias'] }}</td>
                        <td class="celula-num">{{ $linha['justificadas'] }}</td>
                        <td class="celula-num">{{ $linha['nao_informadas'] }}</td>
                        <td class="celula-freq">
                            @if ($linha['percentual'] === null)
                                <span class="sem-dados">— Sem dados</span>
                            @else
                                {{ number_format($linha['percentual'], 1, ',', '.') }}%
                            @endif
                        </td>
                        <td class="celula-observacao">
                            @if ($linha['abaixo_do_limite'])
                                <span class="indicador">&#9888; Abaixo de {{ (int) $limiteIndicador }}%</span>
                            @else
                                &mdash;
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="nota-tabela">
            &#9888; Indicador para análise da Chancelaria. Não representa decisão sobre a situação do Irmão.
        </div>
    @endif

    <div class="criterio">
        <div class="criterio-titulo">Critério deste relatório</div>

        {{-- Verbetes em duas colunas: o mesmo conteúdo em metade da altura,
             o que mantém o fecho do documento na primeira página em
             relatórios curtos. --}}
        <table class="verbetes">
            <tr>
                <td>
                    <b>Sessões:</b> quantidade de sessões do período com lançamento explícito de frequência
                    para o Irmão, e que por isso entram no cálculo do percentual.
                </td>
                <td>
                    <b>Justificadas:</b> sessões com ausência justificada. Permanecem discriminadas em coluna
                    própria, mas contam como ausência no cálculo atual.
                </td>
            </tr>
            <tr>
                <td><b>Presenças:</b> sessões registradas como presença.</td>
                <td>
                    <b>Sem registro:</b> sessões do período sem lançamento de frequência para aquele Irmão.
                    Não integram o cálculo do percentual.
                </td>
            </tr>
            <tr>
                <td><b>Ausências:</b> sessões registradas como ausência.</td>
                <td><b>Frequência:</b> Presenças &divide; Sessões &times; 100.</td>
            </tr>
        </table>

        <p class="ultimo">
            <b>Observação:</b> frequência abaixo de {{ (int) $limiteIndicador }}% é apenas um indicador para
            análise da Chancelaria e não representa decisão automática sobre a situação do Irmão.
        </p>
    </div>

    {{-- A linha de assinatura fica em branco por decisão: o sistema sabe
         quem emitiu o relatório, mas não tem como afirmar quem responde
         pela Chancelaria. --}}
    <table class="fecho">
        <tr>
            <td class="fecho-emissao">
                Emitido em: {{ $emitidoEm->format('d/m/Y') }} às {{ $emitidoEm->format('H:i') }}
                @if (filled($emitidoPor))
                    <br>Emitido por: {{ $emitidoPor }}
                @endif
            </td>
            <td class="fecho-assinatura">
                <div class="assinatura-linha"></div>
                <div class="assinatura-rotulo">Chancelaria</div>
            </td>
        </tr>
    </table>

    {{-- O rodapé institucional e a numeração são desenhados pelo GeradorPdf
         na margem inferior de cada página, e por isso não aparecem aqui. --}}
</body>
</html>
