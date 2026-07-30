<div class="flex h-16 items-center gap-3 border-b border-white/10 px-6">
    <img src="{{ asset('images/logo-loja.png') }}" alt="{{ config('app.name') }}" class="h-10 w-10 rounded-full object-contain ring-2 ring-[#C9A227]/70">
    <span class="text-sm font-semibold leading-tight text-white">{{ config('app.name') }}</span>
</div>

<nav class="space-y-1 px-3 py-4 text-sm">
    <a href="{{ route('area-restrita') }}" class="block rounded-md px-3 py-2 font-medium text-blue-100 hover:bg-[#1B2A4A]">
        Voltar à Área Restrita
    </a>

    @can('usuarios.visualizar')
        <a href="{{ route('admin.usuarios.index') }}" class="block rounded-md px-3 py-2 font-medium {{ request()->routeIs('admin.usuarios.*') ? 'bg-[#C9A227] text-[#14213D]' : 'text-blue-100 hover:bg-[#1B2A4A]' }}">
            Usuários
        </a>
    @endcan

    @can('perfis.visualizar')
        <a href="{{ route('admin.perfis.index') }}" class="block rounded-md px-3 py-2 font-medium {{ request()->routeIs('admin.perfis.*') ? 'bg-[#C9A227] text-[#14213D]' : 'text-blue-100 hover:bg-[#1B2A4A]' }}">
            Perfis e Permissões
        </a>
    @endcan

    @can('irmaos.visualizar')
        <a href="{{ route('admin.irmaos.index') }}" class="block rounded-md px-3 py-2 font-medium {{ request()->routeIs('admin.irmaos.*') ? 'bg-[#C9A227] text-[#14213D]' : 'text-blue-100 hover:bg-[#1B2A4A]' }}">
            Irmãos
        </a>
    @endcan

    @can('cms.visualizar')
        <a href="{{ route('admin.configuracoes.institucional.edit') }}" class="block rounded-md px-3 py-2 font-medium {{ request()->routeIs('admin.configuracoes.*') ? 'bg-[#C9A227] text-[#14213D]' : 'text-blue-100 hover:bg-[#1B2A4A]' }}">
            Configurações do Site
        </a>

        <a href="{{ route('admin.carrossel.index') }}" class="block rounded-md px-3 py-2 font-medium {{ request()->routeIs('admin.carrossel.*') ? 'bg-[#C9A227] text-[#14213D]' : 'text-blue-100 hover:bg-[#1B2A4A]' }}">
            Carrossel
        </a>

        <a href="{{ route('admin.paginas-institucionais.index') }}" class="block rounded-md px-3 py-2 font-medium {{ request()->routeIs('admin.paginas-institucionais.*') ? 'bg-[#C9A227] text-[#14213D]' : 'text-blue-100 hover:bg-[#1B2A4A]' }}">
            Páginas Institucionais
        </a>
    @endcan

    @can('noticias.visualizar')
        <a href="{{ route('admin.noticias.index') }}" class="block rounded-md px-3 py-2 font-medium {{ request()->routeIs('admin.noticias.*') ? 'bg-[#C9A227] text-[#14213D]' : 'text-blue-100 hover:bg-[#1B2A4A]' }}">
            Notícias
        </a>

        <a href="{{ route('admin.noticia-categorias.index') }}" class="block rounded-md px-3 py-2 font-medium {{ request()->routeIs('admin.noticia-categorias.*') ? 'bg-[#C9A227] text-[#14213D]' : 'text-blue-100 hover:bg-[#1B2A4A]' }}">
            Categorias de Notícias
        </a>

        <a href="{{ route('admin.noticia-tags.index') }}" class="block rounded-md px-3 py-2 font-medium {{ request()->routeIs('admin.noticia-tags.*') ? 'bg-[#C9A227] text-[#14213D]' : 'text-blue-100 hover:bg-[#1B2A4A]' }}">
            Tags de Notícias
        </a>
    @endcan

    @can('eventos.visualizar')
        <a href="{{ route('admin.eventos.index') }}" class="block rounded-md px-3 py-2 font-medium {{ request()->routeIs('admin.eventos.index', 'admin.eventos.create', 'admin.eventos.edit') ? 'bg-[#C9A227] text-[#14213D]' : 'text-blue-100 hover:bg-[#1B2A4A]' }}">
            Eventos
        </a>

        <a href="{{ route('admin.eventos.calendario') }}" class="block rounded-md px-3 py-2 font-medium {{ request()->routeIs('admin.eventos.calendario') ? 'bg-[#C9A227] text-[#14213D]' : 'text-blue-100 hover:bg-[#1B2A4A]' }}">
            Calendário
        </a>
    @endcan
    @can('secretaria.visualizar')
        <a href="{{ route('admin.secretaria.documentos.index') }}" class="block rounded-md px-3 py-2 font-medium {{ request()->routeIs('admin.secretaria.*') ? 'bg-[#C9A227] text-[#14213D]' : 'text-blue-100 hover:bg-[#1B2A4A]' }}">
            Secretaria
        </a>
    @endcan
</nav>
