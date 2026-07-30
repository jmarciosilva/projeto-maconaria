<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Support\NormalizadorTexto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SalvarVisitanteChancelariaRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $normalizados = [];

        foreach (['nome', 'loja_origem', 'potencia', 'documento', 'observacao'] as $campo) {
            $valor = $this->input($campo);

            if (is_string($valor)) {
                $normalizados[$campo] = NormalizadorTexto::paraUtf8($valor);
            }
        }

        $this->merge($normalizados);
    }

    public function authorize(): bool
    {
        return $this->user()?->can('chancelaria.criar') === true;
    }

    public function rules(): array
    {
        return [
            'evento_id' => ['nullable', Rule::exists('eventos', 'id')],
            'nome' => ['required', 'string', 'max:255'],
            'loja_origem' => ['nullable', 'string', 'max:255'],
            'potencia' => ['nullable', 'string', 'max:255'],
            'documento' => ['nullable', 'string', 'max:100'],
            'observacao' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
