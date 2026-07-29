@php
$grauOpcoes = collect(\App\Enums\GrauMaconico::cases())->mapWithKeys(fn ($caso) => [$caso->value => $caso->rotulo()])->prepend('Selecione...', '')->all();
$situacaoOpcoes = collect(\App\Enums\SituacaoCadastralIrmao::cases())->mapWithKeys(fn ($caso) => [$caso->value => $caso->rotulo()])->all();
$estadoOpcoes = collect(['' => 'Selecione...'] + array_combine($ufs = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'], $ufs))->all();
$usuarioOpcoes = collect(['' => 'Nenhum'])->union($usuariosDisponiveis)->all();
@endphp

<div class="space-y-8">
    <fieldset class="space-y-4">
        <legend class="text-sm font-semibold text-gray-900">Dados pessoais</legend>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <x-ui.input rotulo="Nome completo" nome="nome_completo" :valor="$irmao->nome_completo ?? null" :erro="$errors->first('nome_completo')" obrigatorio />
            <x-ui.input rotulo="Nome social" nome="nome_social" :valor="$irmao->nome_social ?? null" :erro="$errors->first('nome_social')" />
            <x-ui.input rotulo="Data de nascimento" nome="data_nascimento" tipo="date" :valor="optional($irmao->data_nascimento ?? null)->format('Y-m-d')" :erro="$errors->first('data_nascimento')" />
            <x-ui.input rotulo="CPF" nome="cpf" :valor="$irmao->cpf ?? null" :erro="$errors->first('cpf')" obrigatorio maxlength="11" placeholder="Somente números" />
            <x-ui.input rotulo="RG" nome="rg" :valor="$irmao->rg ?? null" :erro="$errors->first('rg')" placeholder="Somente números" />
            <x-ui.input rotulo="E-mail" nome="email" tipo="email" :valor="$irmao->email ?? null" :erro="$errors->first('email')" />
            <x-ui.input rotulo="Telefone" nome="telefone" :valor="$irmao->telefone ?? null" :erro="$errors->first('telefone')" data-mascara="telefone" maxlength="15" placeholder="(00) 00000-0000" />
        </div>
    </fieldset>

    <fieldset class="space-y-4">
        <legend class="text-sm font-semibold text-gray-900">Endereço</legend>

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
    </fieldset>

    <fieldset class="space-y-4">
        <legend class="text-sm font-semibold text-gray-900">Dados maçônicos</legend>

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
    </fieldset>

    <fieldset class="space-y-4">
        <legend class="text-sm font-semibold text-gray-900">Acesso e fotografia</legend>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <x-ui.select rotulo="Usuário vinculado" nome="usuario_id" :opcoes="$usuarioOpcoes" :valor="$irmao->usuario->id ?? null" :erro="$errors->first('usuario_id')" />

            <div>
                <label for="fotografia" class="block text-sm font-medium text-gray-700">Fotografia</label>
                <input type="file" id="fotografia" name="fotografia" accept="image/*" class="mt-1 block w-full text-sm text-gray-700">
                @error('fotografia')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </fieldset>

    <fieldset class="space-y-4">
        <legend class="text-sm font-semibold text-gray-900">Observações administrativas</legend>
        <p class="text-xs text-gray-500">Visível apenas para quem possui a permissão de visualizar Irmãos.</p>

        <textarea name="observacoes_administrativas" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">{{ old('observacoes_administrativas', $irmao->observacoes_administrativas ?? '') }}</textarea>
    </fieldset>
</div>
