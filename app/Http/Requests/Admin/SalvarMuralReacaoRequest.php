<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\TipoReacaoMural;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SalvarMuralReacaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('mural.visualizar') === true;
    }

    public function rules(): array
    {
        return [
            'tipo' => ['required', Rule::enum(TipoReacaoMural::class)],
        ];
    }
}
