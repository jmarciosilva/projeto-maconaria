<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Enums\TipoReacaoMural;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ReagirMuralRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('sanctum') !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'tipo' => ['required', Rule::enum(TipoReacaoMural::class)],
        ];
    }
}
