@php($evento = $evento ?? null)

@if ($errors->any())
    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4">
        <ul class="list-inside list-disc space-y-1 text-sm text-red-700">
            @foreach ($errors->all() as $erro)
                <li>{{ $erro }}</li>
            @endforeach
        </ul>
    </div>
@endif

<section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
    <h2 class="text-base font-semibold text-gray-900">Dados da sessão</h2>
    <p class="mt-0.5 text-sm text-gray-500">Sessões passadas e futuras são aceitas. A sessão é sempre restrita e nunca aparece no site público.</p>

    <div class="mt-4 grid gap-4 sm:grid-cols-2">
        <x-ui.input
            rotulo="Data e horário"
            nome="inicio_em"
            tipo="datetime-local"
            :valor="old('inicio_em', optional($evento?->inicio_em)->format('Y-m-d\TH:i'))"
            :erro="$errors->first('inicio_em')"
            obrigatorio
        />

        <x-ui.select
            rotulo="Classe da sessão"
            nome="sessao_classe"
            :opcoes="['' => 'Não informada'] + $classesDisponiveis"
            :valor="old('sessao_classe', $evento?->sessao_classe?->value)"
            :erro="$errors->first('sessao_classe')"
        />

        <x-ui.input
            rotulo="Identificação (opcional)"
            nome="titulo"
            :valor="old('titulo', $evento?->titulo)"
            :erro="$errors->first('titulo')"
            placeholder="Ex.: Sessão ordinária de Aprendiz"
        />

        <x-ui.input
            rotulo="Local (opcional)"
            nome="local"
            :valor="old('local', $evento?->local)"
            :erro="$errors->first('local')"
        />
    </div>
</section>
