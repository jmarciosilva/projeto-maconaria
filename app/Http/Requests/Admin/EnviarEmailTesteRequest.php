<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

final class EnviarEmailTesteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('configuracoes.editar') === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'destinatario' => ['required', 'email', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'destinatario.required' => 'Informe um e-mail de destino para o teste.',
            'destinatario.email' => 'Informe um e-mail de destino válido.',
        ];
    }
}
