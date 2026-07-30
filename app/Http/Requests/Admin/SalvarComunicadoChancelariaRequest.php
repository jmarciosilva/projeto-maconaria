<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\StatusComunicadoChancelaria;
use App\Support\NormalizadorTexto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SalvarComunicadoChancelariaRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $normalizados = [];

        foreach (['titulo', 'conteudo'] as $campo) {
            $valor = $this->input($campo);

            if (is_string($valor)) {
                $normalizados[$campo] = NormalizadorTexto::paraUtf8($valor);
            }
        }

        $this->merge($normalizados);
    }

    public function authorize(): bool
    {
        $permissao = $this->isMethod('post') ? 'chancelaria.criar' : 'chancelaria.editar';

        return $this->user()?->can($permissao) === true;
    }

    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:255'],
            'conteudo' => ['nullable', 'string'],
            'status' => ['required', Rule::enum(StatusComunicadoChancelaria::class)],
        ];
    }
}
