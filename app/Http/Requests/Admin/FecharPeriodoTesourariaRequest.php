<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class FecharPeriodoTesourariaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('tesouraria.aprovar') === true;
    }

    public function rules(): array
    {
        return [
            'ano' => ['required', 'integer', 'min:2000', 'max:2100'],
            'mes' => [
                'required',
                'integer',
                'min:1',
                'max:12',
                Rule::unique('tesouraria_fechamentos', 'mes')->where('ano', $this->integer('ano')),
            ],
            'observacao' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
