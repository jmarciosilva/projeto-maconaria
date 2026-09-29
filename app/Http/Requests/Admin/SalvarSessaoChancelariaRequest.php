<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\ClasseSessao;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Registro rápido de uma sessão (geralmente passada) pela Chancelaria, só
 * para poder lançar a frequência dos Irmãos nela — sem passar pelo
 * cadastro completo de Eventos (capa, inscrições, visibilidade pública etc.),
 * que é o módulo usado pela Secretaria para a agenda pública do site.
 */
final class SalvarSessaoChancelariaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('chancelaria.criar') === true;
    }

    public function rules(): array
    {
        return [
            'titulo' => ['nullable', 'string', 'max:255'],
            'inicio_em' => ['required', 'date'],
            'sessao_classe' => ['nullable', Rule::enum(ClasseSessao::class)],
            'local' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'inicio_em.required' => 'Informe a data da sessão.',
        ];
    }
}
