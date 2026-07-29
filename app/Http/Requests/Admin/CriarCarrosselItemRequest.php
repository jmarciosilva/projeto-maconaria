<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

final class CriarCarrosselItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('cms.editar') === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'titulo' => ['nullable', 'string', 'max:255'],
            'subtitulo' => ['nullable', 'string', 'max:255'],
            'imagem_desktop' => ['required', 'image', 'max:4096'],
            'imagem_mobile' => ['nullable', 'image', 'max:4096'],
            'texto_alternativo' => ['required', 'string', 'max:255'],
            'link' => ['nullable', 'url', 'max:255'],
            'texto_botao' => ['nullable', 'string', 'max:50'],
            'abrir_em_nova_aba' => ['boolean'],
            'ordem' => ['nullable', 'integer', 'min:0'],
            'data_inicio' => ['nullable', 'date'],
            'data_fim' => ['nullable', 'date', 'after_or_equal:data_inicio'],
            'ativo' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'imagem_desktop.required' => 'Envie a imagem para desktop.',
            'imagem_desktop.image' => 'A imagem para desktop deve ser um arquivo de imagem.',
            'imagem_mobile.image' => 'A imagem para mobile deve ser um arquivo de imagem.',
            'texto_alternativo.required' => 'Informe o texto alternativo (acessibilidade).',
            'link.url' => 'Informe uma URL válida.',
            'data_fim.after_or_equal' => 'A data final deve ser igual ou posterior à data inicial.',
        ];
    }
}
