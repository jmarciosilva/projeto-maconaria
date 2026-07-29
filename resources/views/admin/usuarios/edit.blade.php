<x-layouts.admin titulo="Editar usuário">
    <form method="POST" action="{{ route('admin.usuarios.update', $usuario) }}" class="max-w-2xl space-y-6 rounded-lg bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')

        <x-ui.input rotulo="Nome" nome="name" :valor="$usuario->name" :erro="$errors->first('name')" obrigatorio />
        <x-ui.input rotulo="E-mail de acesso" nome="email" tipo="email" :valor="$usuario->email" :erro="$errors->first('email')" obrigatorio />
        <x-ui.input rotulo="Telefone" nome="telefone" :valor="$usuario->telefone" :erro="$errors->first('telefone')" />

        <div>
            <span class="block text-sm font-medium text-gray-700">Perfis</span>
            <div class="mt-2 space-y-2">
                @foreach ($perfis as $perfil)
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="perfis[]" value="{{ $perfil }}"
                            @checked(in_array($perfil, old('perfis', $perfisDoUsuario)))
                            class="rounded border-gray-300 text-blue-800 focus:ring-blue-700">
                        {{ $perfil }}
                    </label>
                @endforeach
            </div>
        </div>

        <label class="flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" name="deve_alterar_senha" value="1" @checked(old('deve_alterar_senha', $usuario->deve_alterar_senha)) class="rounded border-gray-300 text-blue-800 focus:ring-blue-700">
            Exigir alteração de senha no próximo login
        </label>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.usuarios.index') }}"><x-ui.button variante="secundario" tipo="button">Cancelar</x-ui.button></a>
            <x-ui.button tipo="submit">Salvar</x-ui.button>
        </div>
    </form>
</x-layouts.admin>
