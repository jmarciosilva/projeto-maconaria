<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

final class AtualizarConfiguracaoInstitucionalRequest extends FormRequest
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
            'nome_loja' => ['nullable', 'string', 'max:255'],
            'titulo_institucional' => ['nullable', 'string', 'max:255'],
            'subtitulo_institucional' => ['nullable', 'string', 'max:255'],
            'telefone_institucional' => ['nullable', 'string', 'regex:/^\(\d{2}\) \d{4,5}-\d{4}$/'],
            'logotipo' => ['nullable', 'image', 'max:2048'],
            'endereco_rodape' => ['nullable', 'string', 'max:500'],
            'email_institucional' => ['nullable', 'email', 'max:255'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'twitter_url' => ['nullable', 'url', 'max:255'],
            'tiktok_url' => ['nullable', 'url', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'logotipo.image' => 'O logotipo deve ser um arquivo de imagem.',
            'logotipo.max' => 'O logotipo não pode ultrapassar 2 MB.',
            'telefone_institucional.regex' => 'Informe o telefone no formato (00) 0000-0000 ou (00) 00000-0000.',
            'email_institucional.email' => 'Informe um e-mail válido.',
            'facebook_url.url' => 'Informe uma URL válida para o Facebook.',
            'instagram_url.url' => 'Informe uma URL válida para o Instagram.',
            'twitter_url.url' => 'Informe uma URL válida para o Twitter/X.',
            'tiktok_url.url' => 'Informe uma URL válida para o TikTok.',
        ];
    }
}
