<x-layouts.site titulo="Contato" meta-descricao="Entre em contato com a ARLS Ferraz de Vasconcelos nº 2516.">
    <section class="bg-brand-paperSoft py-14 lg:py-16">
      <div class="mx-auto max-w-3xl px-5 lg:px-8">
        <div class="text-center">
            <h1 class="font-siteDisplay text-3xl font-bold text-brand-navy sm:text-4xl">Fale com a Loja</h1>
            <p class="mt-3 text-lg text-brand-inkSoft">Tem alguma dúvida, sugestão ou deseja saber mais sobre a Loja? Envie sua mensagem abaixo.</p>
        </div>

        <form method="POST" action="{{ route('contato.enviar') }}" class="mt-10 space-y-6 rounded-xl border border-brand-navy/10 bg-white p-6 shadow-sm sm:p-8">
            @csrf

            {{-- Campo-armadilha: fica invisível para uma pessoa real e nunca deve ser preenchido. --}}
            <div class="hidden" aria-hidden="true">
                <label for="site">Não preencha este campo</label>
                <input type="text" id="site" name="site" tabindex="-1" autocomplete="off">
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <label for="nome" class="block text-sm font-bold text-brand-ink">Nome <span class="text-red-600">*</span></label>
                    <input
                        type="text" id="nome" name="nome" value="{{ old('nome') }}" required
                        class="mt-1.5 block w-full rounded-md border-brand-navy/20 text-[17px] shadow-sm focus:border-brand-navy focus:ring-brand-navy {{ $errors->has('nome') ? 'border-red-400' : '' }}"
                    >
                    @error('nome')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-bold text-brand-ink">E-mail <span class="text-red-600">*</span></label>
                    <input
                        type="email" id="email" name="email" value="{{ old('email') }}" required
                        class="mt-1.5 block w-full rounded-md border-brand-navy/20 text-[17px] shadow-sm focus:border-brand-navy focus:ring-brand-navy {{ $errors->has('email') ? 'border-red-400' : '' }}"
                    >
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <label for="telefone" class="block text-sm font-bold text-brand-ink">Telefone (opcional)</label>
                    <input
                        type="text" id="telefone" name="telefone" value="{{ old('telefone') }}"
                        class="mt-1.5 block w-full rounded-md border-brand-navy/20 text-[17px] shadow-sm focus:border-brand-navy focus:ring-brand-navy {{ $errors->has('telefone') ? 'border-red-400' : '' }}"
                    >
                    @error('telefone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="assunto" class="block text-sm font-bold text-brand-ink">Assunto <span class="text-red-600">*</span></label>
                    <input
                        type="text" id="assunto" name="assunto" value="{{ old('assunto') }}" required
                        class="mt-1.5 block w-full rounded-md border-brand-navy/20 text-[17px] shadow-sm focus:border-brand-navy focus:ring-brand-navy {{ $errors->has('assunto') ? 'border-red-400' : '' }}"
                    >
                    @error('assunto')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="mensagem" class="block text-sm font-bold text-brand-ink">Mensagem <span class="text-red-600">*</span></label>
                <textarea
                    id="mensagem" name="mensagem" rows="6" required
                    class="mt-1.5 block w-full rounded-md border-brand-navy/20 text-[17px] shadow-sm focus:border-brand-navy focus:ring-brand-navy {{ $errors->has('mensagem') ? 'border-red-400' : '' }}"
                >{{ old('mensagem') }}</textarea>
                @error('mensagem')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full rounded-md bg-brand-navy px-6 py-3.5 text-lg font-bold text-white transition hover:bg-brand-navyDeep sm:w-auto">
                Enviar mensagem
            </button>
        </form>
      </div>
    </section>
</x-layouts.site>
