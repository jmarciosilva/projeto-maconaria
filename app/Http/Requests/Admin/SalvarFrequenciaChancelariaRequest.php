<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\StatusFrequencia;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SalvarFrequenciaChancelariaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('chancelaria.editar') === true;
    }

    public function rules(): array
    {
        return [
            'frequencias' => ['array'],
            'frequencias.*.status' => ['nullable', Rule::enum(StatusFrequencia::class)],
            'frequencias.*.observacao' => ['nullable', 'string', 'max:500'],
        ];
    }
}
