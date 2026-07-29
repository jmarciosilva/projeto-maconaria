<x-layouts.admin titulo="Novo usuário">
    <form method="POST" action="{{ route('admin.usuarios.store') }}" class="max-w-2xl space-y-6 rounded-lg bg-white p-6 shadow-sm">
        @csrf

        <x-ui.input rotulo="Nome" nome="name" :erro="$errors->first('name')" obrigatorio />
        <x-ui.input rotulo="E-mail de acesso" nome="email" tipo="email" :erro="$errors->first('email')" obrigatorio />
        <x-ui.input rotulo="Telefone" nome="telefone" :erro="$errors->first('telefone')" />
        <x-ui.input rotulo="Senha inicial" nome="password" tipo="password" :erro="$errors->first('password')" obrigatorio />

        <div>
            <span class="block text-sm font-medium text-gray-700">Perfis</span>
            <div class="mt-2 space-y-2">
                @foreach ($perfis as $perfil)
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="perfis[]" value="{{ $perfil }}" class="rounded border-gray-300 text-blue-800 focus:ring-blue-700">
                        {{ $perfil }}
                    </label>
                @endforeach
            </div>
        </div>

        <label class="flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" name="deve_alterar_senha" value="1" checked class="rounded border-gray-300 text-blue-800 focus:ring-blue-700">
            Exigir alteração de senha no primeiro acesso
        </label>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.usuarios.index') }}"><x-ui.button variante="secundario" tipo="button">Cancelar</x-ui.button></a>
            <x-ui.button tipo="submit">Salvar</x-ui.button>
        </div>
    </form>
</x-layouts.admin>
