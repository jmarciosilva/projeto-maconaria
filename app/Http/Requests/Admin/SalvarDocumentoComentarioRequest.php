<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

final class SalvarDocumentoComentarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('documentos.visualizar') === true;
    }

    public function rules(): array
    {
        return [
            'entrega_id' => ['nullable', 'exists:documento_entregas,id'],
            'comentario' => ['required', 'string', 'max:2000'],
        ];
    }
}
