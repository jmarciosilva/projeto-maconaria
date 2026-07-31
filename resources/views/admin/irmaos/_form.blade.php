@php
$grauOpcoes = collect(\App\Enums\GrauMaconico::cases())->mapWithKeys(fn ($caso) => [$caso->value => $caso->rotulo()])->prepend('Selecione...', '')->all();
$situacaoOpcoes = collect(\App\Enums\SituacaoCadastralIrmao::cases())->mapWithKeys(fn ($caso) => [$caso->value => $caso->rotulo()])->all();
$estadoOpcoes = collect(['' => 'Selecione...'] + array_combine($ufs = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'], $ufs))->all();
$usuarioOpcoes = collect(['' => 'Nenhum'])->union($usuariosDisponiveis)->all();
@endphp

<section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
    <div class="mb-5 flex items-start gap-3">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-700">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
            </svg>
        </span>
        <div>
            <h2 class="text-base font-semibold text-gray-900">Dados pessoais</h2>
            <p class="mt-0.5 text-sm text-gray-500">Identificação civil e contatos do Irmão.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <x-ui.input rotulo="Nome completo" nome="nome_completo" :valor="$irmao->nome_completo ?? null" :erro="$errors->first('nome_completo')" obrigatorio />
        <x-ui.input rotulo="Nome social" nome="nome_social" :valor="$irmao->nome_social ?? null" :erro="$errors->first('nome_social')" />
        <x-ui.input rotulo="Data de nascimento" nome="data_nascimento" tipo="date" :valor="optional($irmao->data_nascimento ?? null)->format('Y-m-d')" :erro="$errors->first('data_nascimento')" />
        <x-ui.input rotulo="CPF" nome="cpf" :valor="$irmao->cpf ?? null" :erro="$errors->first('cpf')" obrigatorio maxlength="11" placeholder="Somente números" />
        <x-ui.input rotulo="RG" nome="rg" :valor="$irmao->rg ?? null" :erro="$errors->first('rg')" placeholder="Somente números" />
        <x-ui.input rotulo="E-mail" nome="email" tipo="email" :valor="$irmao->email ?? null" :erro="$errors->first('email')" />
        <x-ui.input rotulo="Telefone" nome="telefone" :valor="$irmao->telefone ?? null" :erro="$errors->first('telefone')" data-mascara="telefone" maxlength="15" placeholder="(00) 00000-0000" />
    </div>
</section>

<section class="mt-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
    <div class="mb-5 flex items-start gap-3">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-green-100 text-green-700">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
            </svg>
        </span>
        <div>
            <h2 class="text-base font-semibold text-gray-900">Endereço</h2>
            <p class="mt-0.5 text-sm text-gray-500">Endereço residencial do Irmão.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
            <x-ui.input
                rotulo="CEP"
                nome="cep"
                :valor="$irmao->cep ?? null"
                :erro="$errors->first('cep')"
                data-mascara="cep"
                data-busca-cep
                maxlength="9"
                placeholder="Somente números"
                autocomplete="off"
            />
            <p id="status-cep" class="mt-1 text-sm" aria-live="polite"></p>
        </div>

        <x-ui.input rotulo="Endereço" nome="endereco" :valor="$irmao->endereco ?? null" :erro="$errors->first('endereco')" />
        <x-ui.input rotulo="Número" nome="numero" :valor="$irmao->numero ?? null" :erro="$errors->first('numero')" />
        <x-ui.input rotulo="Complemento" nome="complemento" :valor="$irmao->complemento ?? null" :erro="$errors->first('complemento')" />
        <x-ui.input rotulo="Bairro" nome="bairro" :valor="$irmao->bairro ?? null" :erro="$errors->first('bairro')" />
        <x-ui.input rotulo="Cidade" nome="cidade" :valor="$irmao->cidade ?? null" :erro="$errors->first('cidade')" />
        <x-ui.select rotulo="Estado (UF)" nome="estado" :opcoes="$estadoOpcoes" :valor="$irmao->estado ?? null" :erro="$errors->first('estado')" />
    </div>
</section>

<section class="mt-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
    <div class="mb-5 flex items-start gap-3">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-indigo-700">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6-10.125a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0Zm1.294 6.336a6.721 6.721 0 0 1-3.17.789 6.721 6.721 0 0 1-3.168-.789 3.376 3.376 0 0 1 6.338 0Z" />
            </svg>
        </span>
        <div>
            <h2 class="text-base font-semibold text-gray-900">Dados maçônicos</h2>
            <p class="mt-0.5 text-sm text-gray-500">Matrícula, grau, cargo e datas de percurso na Loja.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <x-ui.input rotulo="CIM / Matrícula" nome="cim" :valor="$irmao->cim ?? null" :erro="$errors->first('cim')" />
        <x-ui.select rotulo="Grau atual" nome="grau_atual" :opcoes="$grauOpcoes" :valor="$irmao->grau_atual?->value ?? null" :erro="$errors->first('grau_atual')" />
        <x-ui.select rotulo="Situação cadastral" nome="situacao_cadastral" :opcoes="$situacaoOpcoes" :valor="$irmao->situacao_cadastral?->value ?? 'ativo'" :erro="$errors->first('situacao_cadastral')" obrigatorio />
        <x-ui.input rotulo="Cargo atual" nome="cargo_atual" :valor="$irmao->cargo_atual ?? null" :erro="$errors->first('cargo_atual')" />
        <x-ui.input rotulo="Data de iniciação" nome="data_iniciacao" tipo="date" :valor="optional($irmao->data_iniciacao ?? null)->format('Y-m-d')" :erro="$errors->first('data_iniciacao')" />
        <x-ui.input rotulo="Data de elevação" nome="data_elevacao" tipo="date" :valor="optional($irmao->data_elevacao ?? null)->format('Y-m-d')" :erro="$errors->first('data_elevacao')" />
        <x-ui.input rotulo="Data de exaltação" nome="data_exaltacao" tipo="date" :valor="optional($irmao->data_exaltacao ?? null)->format('Y-m-d')" :erro="$errors->first('data_exaltacao')" />
        <x-ui.input rotulo="Data de ingresso na Loja" nome="data_ingresso_loja" tipo="date" :valor="optional($irmao->data_ingresso_loja ?? null)->format('Y-m-d')" :erro="$errors->first('data_ingresso_loja')" />
        <x-ui.input rotulo="Data de desligamento" nome="data_desligamento" tipo="date" :valor="optional($irmao->data_desligamento ?? null)->format('Y-m-d')" :erro="$errors->first('data_desligamento')" />
    </div>
</section>

<section class="mt-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
    <div class="mb-5 flex items-start gap-3">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-purple-100 text-purple-700">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Z" />
            </svg>
        </span>
        <div>
            <h2 class="text-base font-semibold text-gray-900">Acesso e fotografia</h2>
            <p class="mt-0.5 text-sm text-gray-500">Conta de usuário vinculada para login e foto de perfil do Irmão.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <x-ui.select rotulo="Usuário vinculado" nome="usuario_id" :opcoes="$usuarioOpcoes" :valor="$irmao->usuario->id ?? null" :erro="$errors->first('usuario_id')" />

        <div x-data="{ nomeArquivo: null }">
            <label class="block text-sm font-medium text-gray-700">Fotografia</label>

            <div class="mt-1.5 flex items-center gap-4">
                <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-full border border-gray-200 bg-gray-50">
                    @if (isset($irmao) && $irmao->fotografia)
                        <img src="{{ route('admin.irmaos.foto', $irmao) }}" alt="Fotografia de {{ $irmao->nome_completo }}" class="h-full w-full object-cover">
                    @else
                        <svg class="h-7 w-7 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                    @endif
                </div>

                <div>
                    <label for="fotografia" class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                        Escolher foto
                    </label>
                    <input type="file" id="fotografia" name="fotografia" accept="image/*" class="sr-only" @change="nomeArquivo = $event.target.files[0]?.name ?? null">
                    <p class="mt-1.5 text-xs text-gray-500" x-text="nomeArquivo ?? 'Nenhum arquivo selecionado.'"></p>
                </div>
            </div>
            @error('fotografia')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
</section>

<section class="mt-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
    <div class="mb-5 flex items-start gap-3">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gray-100 text-gray-600">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg>
        </span>
        <div>
            <h2 class="text-base font-semibold text-gray-900">Observações administrativas</h2>
            <p class="mt-0.5 text-sm text-gray-500">Visível apenas para quem possui a permissão de visualizar Irmãos.</p>
        </div>
    </div>

    <textarea name="observacoes_administrativas" rows="4" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">{{ old('observacoes_administrativas', $irmao->observacoes_administrativas ?? '') }}</textarea>
</section>
