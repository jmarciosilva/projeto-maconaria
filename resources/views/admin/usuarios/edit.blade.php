<x-layouts.admin titulo="Editar usuário">
    <form method="POST" action="{{ route('admin.usuarios.update', $usuario) }}" class="max-w-3xl">
        @csrf
        @method('PUT')

        <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <div class="mb-5 flex items-start gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                </span>
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Dados de acesso</h2>
                    <p class="mt-0.5 text-sm text-gray-500">Identificação e credenciais usadas para entrar no sistema.</p>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.input rotulo="Nome" nome="name" :valor="$usuario->name" :erro="$errors->first('name')" obrigatorio />
                <x-ui.input rotulo="E-mail" nome="email" tipo="email" :valor="$usuario->email" :erro="$errors->first('email')" obrigatorio />
                <x-ui.input
                    rotulo="Código CIM"
                    nome="codigo_cim"
                    :valor="$usuario->codigo_cim"
                    :erro="$errors->first('codigo_cim')"
                    obrigatorio
                    inputmode="numeric"
                    pattern="\d{6}"
                    maxlength="6"
                    placeholder="000000"
                />
                <x-ui.input rotulo="Telefone" nome="telefone" :valor="$usuario->telefone" :erro="$errors->first('telefone')" data-mascara="telefone" maxlength="15" placeholder="(00) 00000-0000" />
            </div>

            <p class="mt-3 text-xs text-gray-500">O Código CIM (Código Irmão Maçom) tem 6 dígitos e é o que o usuário usa para entrar no sistema — o e-mail não serve mais para login.</p>
        </section>

        <section class="mt-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <div class="mb-5 flex items-start gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-purple-100 text-purple-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                    </svg>
                </span>
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Perfis de acesso</h2>
                    <p class="mt-0.5 text-sm text-gray-500">Define quais áreas e permissões do painel este usuário terá.</p>
                </div>
            </div>

            <div class="space-y-2.5">
                @foreach ($perfis as $perfil)
                    <label class="flex items-center justify-between gap-3 rounded-md border border-gray-200 px-4 py-3">
                        <span class="text-sm font-medium text-gray-700">{{ $perfil }}</span>
                        <input type="checkbox" name="perfis[]" value="{{ $perfil }}"
                            @checked(in_array($perfil, old('perfis', $perfisDoUsuario)))
                            class="rounded border-gray-300 text-blue-800 focus:ring-blue-700">
                    </label>
                @endforeach
            </div>
        </section>

        @if ($podeAtribuirPermissoes)
            <section class="mt-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <div class="mb-5 flex items-start gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-teal-100 text-teal-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 0 1 0 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 0 1 0-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.281Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">Permissões individuais</h2>
                        <p class="mt-0.5 text-sm text-gray-500">Além do que os perfis acima já liberam, conceda permissões extras só para este usuário — sem precisar criar um novo perfil.</p>
                    </div>
                </div>

                <div class="space-y-2">
                    @foreach ($permissoesAgrupadas as $rotuloModulo => $permissoesDoModulo)
                        <details class="rounded-md border border-gray-200">
                            <summary class="cursor-pointer select-none px-4 py-2.5 text-sm font-semibold text-gray-700">{{ $rotuloModulo }}</summary>
                            <div class="grid gap-2 border-t border-gray-100 p-4 sm:grid-cols-2">
                                @foreach ($permissoesDoModulo as $permissao)
                                    <label class="flex items-center justify-between gap-3 rounded-md border border-gray-200 px-3 py-2">
                                        <span class="text-sm text-gray-700">{{ $permissao->name }}</span>
                                        <input type="checkbox" name="permissoes[]" value="{{ $permissao->name }}"
                                            @checked(in_array($permissao->name, old('permissoes', $permissoesDoUsuario)))
                                            class="rounded border-gray-300 text-blue-800 focus:ring-blue-700">
                                    </label>
                                @endforeach
                            </div>
                        </details>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="mt-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <div class="mb-5 flex items-start gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                    </svg>
                </span>
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Segurança</h2>
                    <p class="mt-0.5 text-sm text-gray-500">Configurações relacionadas à senha desta conta.</p>
                </div>
            </div>

            <label class="flex items-center justify-between gap-3 rounded-md border border-gray-200 px-4 py-3">
                <span class="text-sm font-medium text-gray-700">Exigir alteração de senha no próximo login</span>
                <input type="checkbox" name="deve_alterar_senha" value="1" @checked(old('deve_alterar_senha', $usuario->deve_alterar_senha)) class="rounded border-gray-300 text-blue-800 focus:ring-blue-700">
            </label>
        </section>

        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('admin.usuarios.index') }}"><x-ui.button variante="secundario" tipo="button">Cancelar</x-ui.button></a>
            <x-ui.button tipo="submit">Salvar</x-ui.button>
        </div>
    </form>
</x-layouts.admin>
