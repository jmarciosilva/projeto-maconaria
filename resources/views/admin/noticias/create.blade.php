<x-layouts.admin titulo="Nova Notícia">
    <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-4">
        <div class="flex gap-3">
            <svg class="h-5 w-5 shrink-0 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <div>
                <h3 class="text-sm font-semibold text-amber-900">Dica: Como criar uma notícia</h3>
                <ul class="mt-2 list-inside list-decimal space-y-1 text-sm text-amber-800">
                    <li><strong>Preencha o título</strong> — será exibido no site (máximo 255 caracteres)</li>
                    <li><strong>Crie um slug</strong> — URL amigável com apenas letras, números e hífens</li>
                    <li><strong>Escolha uma categoria</strong> — para organizar suas notícias</li>
                    <li><strong>Adicione um resumo</strong> — exibido nas listagens (máximo 500 caracteres)</li>
                    <li><strong>Escreva o conteúdo</strong> — use formatação rica (bold, itálico, listas, etc)</li>
                    <li><strong>Escolha o status</strong> — Rascunho, Publicada ou Agendada</li>
                </ul>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.noticias.store') }}" enctype="multipart/form-data" class="max-w-4xl">
        @csrf

        @include('admin.noticias._form')

        <div class="mt-6 flex gap-3">
            <x-ui.button tipo="submit">
                <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Salvar notícia
            </x-ui.button>
            <a href="{{ route('admin.noticias.index') }}">
                <x-ui.button variante="secundario">Cancelar</x-ui.button>
            </a>
        </div>
    </form>
</x-layouts.admin>
