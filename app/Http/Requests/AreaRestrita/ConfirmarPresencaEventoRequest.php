<?php

declare(strict_types=1);

namespace App\Http\Requests\AreaRestrita;

use Illuminate\Foundation\Http\FormRequest;

final class ConfirmarPresencaEventoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'observacao' => ['nullable', 'string', 'max:500'],
        ];
    }
}
