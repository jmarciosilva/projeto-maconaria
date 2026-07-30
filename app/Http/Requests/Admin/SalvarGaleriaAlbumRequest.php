<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\StatusMuralGaleria;
use App\Enums\VisibilidadeMuralGaleria;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SalvarGaleriaAlbumRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can($this->isMethod('post') ? 'galeria.criar' : 'galeria.editar') === true;
    }

    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'descricao' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::enum(StatusMuralGaleria::class)],
            'visibilidade' => ['required', Rule::enum(VisibilidadeMuralGaleria::class)],
            'fotografias' => ['nullable', 'array'],
            'fotografias.*' => ['image', 'max:5120'],
            'textos_alternativos' => ['nullable', 'array'],
            'textos_alternativos.*' => ['nullable', 'string', 'max:255'],
        ];
    }
}
