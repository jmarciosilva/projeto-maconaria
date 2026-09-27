<div class="flex h-16 items-center gap-3 border-b border-white/10 px-6">
    <img src="{{ asset('images/logo-loja.png') }}" alt="{{ config('app.name') }}" class="h-10 w-10 rounded-full object-contain ring-2 ring-[#C9A227]/70">
    <span class="text-sm font-semibold leading-tight text-white">{{ config('app.name') }}</span>
</div>

<nav class="space-y-1 px-3 py-4 text-sm">
    <x-ui.nav-link :href="route('admin.dashboard')" :ativo="request()->routeIs('admin.dashboard')">
        Dashboard
    </x-ui.nav-link>

    <x-ui.nav-link :href="route('area-restrita')">
        Voltar para Mural da Loja
    </x-ui.nav-link>

    @canany(['usuarios.visualizar', 'perfis.visualizar', 'irmaos.visualizar'])
        <x-ui.nav-grupo titulo="Gestão de acesso" />

        @can('usuarios.visualizar')
            <x-ui.nav-link :href="route('admin.usuarios.index')" :ativo="request()->routeIs('admin.usuarios.*')">
                Usuários
            </x-ui.nav-link>
        @endcan

        @can('perfis.visualizar')
            <x-ui.nav-link :href="route('admin.perfis.index')" :ativo="request()->routeIs('admin.perfis.*')">
                Perfis e Permissões
            </x-ui.nav-link>
        @endcan

        @can('irmaos.visualizar')
            <x-ui.nav-link :href="route('admin.irmaos.index')" :ativo="request()->routeIs('admin.irmaos.*')">
                Irmãos
            </x-ui.nav-link>
        @endcan
    @endcanany

    @canany(['recados.visualizar', 'eventos.visualizar', 'mural.visualizar'])
        <x-ui.nav-grupo titulo="Mural e comunicação" />

        @can('recados.visualizar')
            <x-ui.nav-link :href="route('admin.recados.index')" :ativo="request()->routeIs('admin.recados.*')">
                Recados do Painel
            </x-ui.nav-link>
        @endcan

        @can('mural.visualizar')
            <x-ui.nav-link :href="route('admin.mural.publicacoes.index')" :ativo="request()->routeIs('admin.mural.*')">
                Mural
            </x-ui.nav-link>
        @endcan

        @can('eventos.visualizar')
            <x-ui.nav-link :href="route('admin.eventos.index')" :ativo="request()->routeIs('admin.eventos.index', 'admin.eventos.create', 'admin.eventos.edit')">
                Eventos
            </x-ui.nav-link>

            <x-ui.nav-link :href="route('admin.eventos.calendario')" :ativo="request()->routeIs('admin.eventos.calendario')">
                Calendário
            </x-ui.nav-link>
        @endcan
    @endcanany

    @canany(['cms.visualizar', 'noticias.visualizar', 'galeria.visualizar'])
        <x-ui.nav-grupo titulo="Site público" />

        @can('cms.visualizar')
            <x-ui.nav-link :href="route('admin.configuracoes.institucional.edit')" :ativo="request()->routeIs('admin.configuracoes.institucional.*')">
                Configurações do Site
            </x-ui.nav-link>

<x-ui.nav-link :href="route('admin.paginas-institucionais.index')" :ativo="request()->routeIs('admin.paginas-institucionais.*')">
                Páginas Institucionais
            </x-ui.nav-link>
        @endcan

        @can('noticias.visualizar')
            <x-ui.nav-link :href="route('admin.noticias.index')" :ativo="request()->routeIs('admin.noticias.*')">
                Notícias
            </x-ui.nav-link>

            <x-ui.nav-link :href="route('admin.noticia-categorias.index')" :ativo="request()->routeIs('admin.noticia-categorias.*')">
                Categorias de Notícias
            </x-ui.nav-link>

            <x-ui.nav-link :href="route('admin.noticia-tags.index')" :ativo="request()->routeIs('admin.noticia-tags.*')">
                Tags de Notícias
            </x-ui.nav-link>
        @endcan

        @can('galeria.visualizar')
            <x-ui.nav-link :href="route('admin.galeria.albuns.index')" :ativo="request()->routeIs('admin.galeria.*')">
                Galeria
            </x-ui.nav-link>
        @endcan
    @endcanany

    @canany(['secretaria.visualizar', 'chancelaria.visualizar', 'tesouraria.visualizar', 'documentos.visualizar'])
        <x-ui.nav-grupo titulo="Administração da Loja" />

        @can('secretaria.visualizar')
            <x-ui.nav-link :href="route('admin.secretaria.documentos.index')" :ativo="request()->routeIs('admin.secretaria.*')">
                Secretaria
            </x-ui.nav-link>
        @endcan

        @can('chancelaria.visualizar')
            <x-ui.nav-link :href="route('admin.chancelaria.index')" :ativo="request()->routeIs('admin.chancelaria.*')">
                Chancelaria
            </x-ui.nav-link>
        @endcan

        @can('tesouraria.visualizar')
            <x-ui.nav-link :href="route('admin.tesouraria.index')" :ativo="request()->routeIs('admin.tesouraria.*')">
                Tesouraria
            </x-ui.nav-link>
        @endcan

        @can('documentos.visualizar')
            <x-ui.nav-link :href="route('admin.documentos.atividades.index')" :ativo="request()->routeIs('admin.documentos.*')">
                Documentos e Trabalhos
            </x-ui.nav-link>
        @endcan
    @endcanany

    @can('configuracoes.visualizar')
        <x-ui.nav-grupo titulo="Sistema" />

        <x-ui.nav-link :href="route('admin.configuracoes.email.edit')" :ativo="request()->routeIs('admin.configuracoes.email.*')">
            Configurações de E-mail
        </x-ui.nav-link>
    @endcan
</nav>
