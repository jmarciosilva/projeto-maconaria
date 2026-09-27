<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CriarUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', User::class) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'codigo_cim' => ['required', 'string', 'regex:/^\d{6}$/', Rule::unique('users', 'codigo_cim')],
            'telefone' => ['nullable', 'string', 'regex:/^\(\d{2}\) \d{5}-\d{4}$/'],
            'password' => ['required', 'string', 'min:8'],
            'deve_alterar_senha' => ['boolean'],
            'perfis' => ['array'],
            'perfis.*' => ['string', Rule::exists('roles', 'name')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Informe o nome do usuário.',
            'email.required' => 'Informe o e-mail do usuário.',
            'email.unique' => 'Já existe um usuário cadastrado com este e-mail.',
            'codigo_cim.required' => 'Informe o código CIM (Código Irmão Maçom).',
            'codigo_cim.regex' => 'O código CIM deve ter exatamente 6 dígitos numéricos.',
            'codigo_cim.unique' => 'Já existe um usuário cadastrado com este código CIM.',
            'password.required' => 'Informe uma senha inicial.',
            'password.min' => 'A senha deve ter pelo menos 8 caracteres.',
            'telefone.regex' => 'Informe o telefone no formato (00) 00000-0000.',
        ];
    }
}
