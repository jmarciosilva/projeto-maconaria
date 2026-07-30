<x-layouts.admin titulo="Configurações de E-mail">
    <div class="max-w-2xl space-y-8">
        <form method="POST" action="{{ route('admin.configuracoes.email.update') }}" class="space-y-8 rounded-lg bg-white p-6 shadow-sm">
            @csrf
            @method('PUT')

            <fieldset class="space-y-4">
                <legend class="text-sm font-semibold text-gray-900">Servidor de envio</legend>

                <x-ui.select
                    rotulo="Tipo de envio"
                    nome="mailer"
                    :opcoes="['smtp' => 'SMTP', 'log' => 'Log (não envia de verdade — uso em desenvolvimento)']"
                    :valor="$configuracao->mailer->value"
                    :erro="$errors->first('mailer')"
                    obrigatorio
                />

                <x-ui.input rotulo="Servidor (host)" nome="host" :valor="$configuracao->host" :erro="$errors->first('host')" placeholder="smtp.exemplo.com" />
                <x-ui.input rotulo="Porta" nome="porta" tipo="number" :valor="$configuracao->porta" :erro="$errors->first('porta')" placeholder="587" />
                <x-ui.input rotulo="Usuário" nome="usuario" :valor="$configuracao->usuario" :erro="$errors->first('usuario')" />

                <div>
                    <x-ui.input
                        rotulo="Senha"
                        nome="senha"
                        tipo="password"
                        :erro="$errors->first('senha')"
                        placeholder="{{ $configuracao->possuiSenhaConfigurada() ? 'Deixe em branco para manter a senha atual' : '' }}"
                    />
                    @if ($configuracao->possuiSenhaConfigurada())
                        <p class="mt-1 text-xs text-gray-500">Uma senha já está configurada. Preencha apenas se quiser substituí-la.</p>
                    @endif
                </div>

                <x-ui.select
                    rotulo="Criptografia"
                    nome="criptografia"
                    :opcoes="['' => 'Nenhuma', 'tls' => 'TLS', 'ssl' => 'SSL']"
                    :valor="$configuracao->criptografia?->value"
                    :erro="$errors->first('criptografia')"
                />

                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="hidden" name="ativo" value="0">
                    <input type="checkbox" name="ativo" value="1" @checked(old('ativo', $configuracao->ativo)) class="rounded border-gray-300 text-blue-900 focus:ring-blue-900">
                    Usar esta configuração para o envio de e-mails do sistema
                </label>
            </fieldset>

            <fieldset class="space-y-4">
                <legend class="text-sm font-semibold text-gray-900">Remetente</legend>

                <x-ui.input rotulo="Nome do remetente" nome="remetente_nome" :valor="$configuracao->remetente_nome" :erro="$errors->first('remetente_nome')" obrigatorio />
                <x-ui.input rotulo="E-mail do remetente" nome="remetente_email" tipo="email" :valor="$configuracao->remetente_email" :erro="$errors->first('remetente_email')" obrigatorio />
            </fieldset>

            <div class="flex justify-end">
                @can('configuracoes.editar')
                    <x-ui.button tipo="submit">Salvar</x-ui.button>
                @endcan
            </div>
        </form>

        @can('configuracoes.editar')
            <form method="POST" action="{{ route('admin.configuracoes.email.teste') }}" class="space-y-4 rounded-lg bg-white p-6 shadow-sm">
                @csrf

                <fieldset class="space-y-4">
                    <legend class="text-sm font-semibold text-gray-900">Enviar e-mail de teste</legend>
                    <p class="text-sm text-gray-500">Salve as configurações acima antes de testar o envio.</p>

                    <x-ui.input rotulo="Enviar teste para" nome="destinatario" tipo="email" :erro="$errors->first('destinatario')" obrigatorio />
                </fieldset>

                <div class="flex justify-end">
                    <x-ui.button tipo="submit" variante="secundario">Enviar teste</x-ui.button>
                </div>
            </form>
        @endcan
    </div>
</x-layouts.admin>
