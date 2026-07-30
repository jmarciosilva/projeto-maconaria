<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\TipoContaFinanceira;
use App\Support\Tesouraria\ConversorMoeda;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SalvarTesourariaContaRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'saldo_inicial_centavos' => ConversorMoeda::paraCentavos($this->input('saldo_inicial')),
            'ativa' => $this->boolean('ativa'),
        ]);
    }

    public function authorize(): bool
    {
        return $this->user()?->can('tesouraria.editar') === true;
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:120'],
            'instituicao' => ['nullable', 'string', 'max:120'],
            'tipo' => ['required', Rule::enum(TipoContaFinanceira::class)],
            'saldo_inicial_centavos' => ['integer'],
            'ativa' => ['boolean'],
        ];
    }
}
