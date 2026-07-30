<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

final class SalvarMuralComentarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('mural.visualizar') === true;
    }

    public function rules(): array
    {
        return [
            'comentario' => ['required', 'string', 'max:1000'],
        ];
    }
}
