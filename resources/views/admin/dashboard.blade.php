<x-layouts.admin titulo="Painel">
    <div class="mb-6">
        <p class="text-sm text-gray-600">Bem-vindo(a), {{ auth()->user()->name }}. Aqui está um resumo geral do sistema.</p>
    </div>

    @if (empty($dados))
        <x-ui.empty-state titulo="Nenhum indicador disponível" descricao="Seu perfil não tem permissão de visualização em nenhum módulo com indicadores." />
    @endif

    @if (isset($dados['usuarios']))
        <section class="mb-8">
            <h2 class="mb-3 text-sm font-bold uppercase tracking-wide text-gray-500">Usuários</h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <x-ui.kpi-card rotulo="Total de usuários" :valor="$dados['usuarios']['total']" cor="azul" icone="usuarios" />
                <x-ui.kpi-card rotulo="Ativos" :valor="$dados['usuarios']['ativos']" cor="verde" icone="usuario-check" />
                <x-ui.kpi-card rotulo="Inativos" :valor="$dados['usuarios']['inativos']" cor="cinza" icone="usuarios" />
                <x-ui.kpi-card rotulo="Bloqueados" :valor="$dados['usuarios']['bloqueados']" cor="vermelho" icone="cadeado" />
            </div>
        </section>
    @endif

    @if (isset($dados['tesouraria']))
        <section class="mb-8">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-sm font-bold uppercase tracking-wide text-gray-500">Tesouraria</h2>
                <a href="{{ route('admin.tesouraria.index') }}" class="text-sm font-semibold text-blue-800 hover:underline">Ver tesouraria →</a>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <x-ui.kpi-card rotulo="Receitas baixadas" :valor="'R$ '.\App\Support\Tesouraria\ConversorMoeda::formatar($dados['tesouraria']['receitas'])" cor="verde" icone="dinheiro" />
                <x-ui.kpi-card rotulo="Despesas baixadas" :valor="'R$ '.\App\Support\Tesouraria\ConversorMoeda::formatar($dados['tesouraria']['despesas'])" cor="vermelho" icone="dinheiro" />
                <x-ui.kpi-card rotulo="Saldo" :valor="'R$ '.\App\Support\Tesouraria\ConversorMoeda::formatar($dados['tesouraria']['saldo'])" cor="azul" icone="saldo" />
                <x-ui.kpi-card rotulo="Lançamentos pendentes" :valor="$dados['tesouraria']['pendentes']" cor="ambar" icone="pendente" />
            </div>
        </section>
    @endif

    @if (isset($dados['chancelaria']))
        <section class="mb-8">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-sm font-bold uppercase tracking-wide text-gray-500">Chancelaria</h2>
                <a href="{{ route('admin.chancelaria.index') }}" class="text-sm font-semibold text-blue-800 hover:underline">Ver chancelaria →</a>
            </div>
            <p class="mb-3 text-xs text-gray-500">Últimos 3 meses.</p>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <x-ui.kpi-card rotulo="Presenças registradas" :valor="$dados['chancelaria']['presencas']" cor="verde" icone="presenca" />
                <x-ui.kpi-card rotulo="Ausências/justificadas" :valor="$dados['chancelaria']['ausencias']" cor="ambar" icone="usuarios" />
                <x-ui.kpi-card rotulo="Visitantes recebidos" :valor="$dados['chancelaria']['visitantes']" cor="roxo" icone="visitante" />
            </div>
        </section>
    @endif

    @if (isset($dados['conteudo']))
        <section class="mb-8">
            <h2 class="mb-3 text-sm font-bold uppercase tracking-wide text-gray-500">Conteúdo do site</h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @if ($dados['conteudo']['noticias'] !== null)
                    <x-ui.kpi-card rotulo="Notícias publicadas" :valor="$dados['conteudo']['noticias']" cor="azul" icone="jornal" />
                @endif

                @if ($dados['conteudo']['eventos'] !== null)
                    <x-ui.kpi-card rotulo="Próximos eventos" :valor="$dados['conteudo']['eventos']" cor="roxo" icone="calendario" />
                @endif

                @if ($dados['conteudo']['comentariosPendentes'] !== null)
                    <x-ui.kpi-card rotulo="Comentários do mural pendentes" :valor="$dados['conteudo']['comentariosPendentes']" cor="ambar" icone="mural" />
                @endif
            </div>
        </section>
    @endif
</x-layouts.admin>
