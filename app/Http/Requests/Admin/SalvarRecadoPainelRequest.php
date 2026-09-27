<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\CategoriaRecadoPainel;
use App\Support\NormalizadorTexto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SalvarRecadoPainelRequest extends FormRequest
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

        $normalizados['ativo'] = $this->boolean('ativo');

        $this->merge($normalizados);
    }

    public function authorize(): bool
    {
        $permissao = $this->isMethod('post') ? 'recados.criar' : 'recados.editar';

        return $this->user()?->can($permissao) === true;
    }

    public function rules(): array
    {
        return [
            'categoria' => ['required', Rule::enum(CategoriaRecadoPainel::class)],
            'titulo' => ['required', 'string', 'max:255'],
            'conteudo' => ['nullable', 'string'],
            'ativo' => ['boolean'],
            'publicado_em' => ['nullable', 'date'],
            'valido_ate' => ['nullable', 'date'],
        ];
    }
}
