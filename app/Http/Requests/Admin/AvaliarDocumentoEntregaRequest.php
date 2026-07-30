<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

final class AvaliarDocumentoEntregaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('documentos.avaliar') === true;
    }

    public function rules(): array
    {
        return [
            'nota' => ['nullable', 'integer', 'min:0', 'max:100'],
            'parecer' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
