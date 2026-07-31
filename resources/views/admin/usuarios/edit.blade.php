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
                <x-ui.input rotulo="E-mail de acesso" nome="email" tipo="email" :valor="$usuario->email" :erro="$errors->first('email')" obrigatorio />
                <div class="sm:col-span-2">
                    <x-ui.input rotulo="Telefone" nome="telefone" :valor="$usuario->telefone" :erro="$errors->first('telefone')" data-mascara="telefone" maxlength="15" placeholder="(00) 00000-0000" />
                </div>
            </div>
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
