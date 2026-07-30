<?php

declare(strict_types=1);

namespace App\Http\Requests\Site;

use App\Support\NormalizadorTexto;
use Illuminate\Foundation\Http\FormRequest;

final class EnviarContatoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $normalizados = [];

        foreach (['nome', 'assunto', 'mensagem'] as $campo) {
            $valor = $this->input($campo);

            if (is_string($valor)) {
                $normalizados[$campo] = NormalizadorTexto::paraUtf8($valor);
            }
        }

        $this->merge($normalizados);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'assunto' => ['required', 'string', 'max:255'],
            'mensagem' => ['required', 'string', 'max:5000'],
            // Campo-armadilha (honeypot): invisível para uma pessoa real,
            // preenchido apenas por robôs de spam. Nunca exibido nem exigido
            // — se vier preenchido, ContatoController descarta silenciosamente.
            'site' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nome.required' => 'Informe seu nome.',
            'email.required' => 'Informe um e-mail para retorno.',
            'email.email' => 'Informe um e-mail válido.',
            'assunto.required' => 'Informe o assunto da mensagem.',
            'mensagem.required' => 'Escreva sua mensagem.',
        ];
    }
}
