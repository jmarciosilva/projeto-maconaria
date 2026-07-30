<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SalvarConfiguracaoEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('configuracoes.editar') === true;
    }

    /**
     * O <select> de criptografia envia "" para "Nenhuma". Convertido para
     * null aqui — do contrário, o cast de enum nullable do Model
     * (CriptografiaEmail::class) rejeita "" com um ValueError ao salvar,
     * pois "" não é um valor válido do enum.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'criptografia' => $this->input('criptografia') !== '' ? $this->input('criptografia') : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'mailer' => ['required', Rule::in(['smtp', 'log'])],
            'host' => ['nullable', 'string', 'max:255', 'required_if:mailer,smtp'],
            'porta' => ['nullable', 'integer', 'min:1', 'max:65535', 'required_if:mailer,smtp'],
            'usuario' => ['nullable', 'string', 'max:255'],
            // Em branco mantém a senha já cadastrada — ver ConfiguracaoEmailController::update().
            'senha' => ['nullable', 'string', 'max:255'],
            'criptografia' => ['nullable', Rule::in(['tls', 'ssl'])],
            'remetente_nome' => ['required', 'string', 'max:255'],
            'remetente_email' => ['required', 'email', 'max:255'],
            'ativo' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'mailer.required' => 'Selecione o tipo de envio de e-mail.',
            'mailer.in' => 'Selecione um tipo de envio válido.',
            'host.required_if' => 'Informe o servidor SMTP.',
            'porta.required_if' => 'Informe a porta do servidor SMTP.',
            'porta.integer' => 'A porta deve ser um número.',
            'remetente_nome.required' => 'Informe o nome do remetente.',
            'remetente_email.required' => 'Informe o e-mail do remetente.',
            'remetente_email.email' => 'Informe um e-mail de remetente válido.',
            'criptografia.in' => 'Selecione uma criptografia válida.',
        ];
    }
}
