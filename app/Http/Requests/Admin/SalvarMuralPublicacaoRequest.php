<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\StatusMuralGaleria;
use App\Enums\VisibilidadeMuralGaleria;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SalvarMuralPublicacaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can($this->isMethod('post') ? 'mural.criar' : 'mural.editar') === true;
    }

    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:255'],
            'conteudo' => ['required', 'string', 'max:5000'],
            'status' => ['required', Rule::enum(StatusMuralGaleria::class)],
            'visibilidade' => ['required', Rule::enum(VisibilidadeMuralGaleria::class)],
        ];
    }
}
