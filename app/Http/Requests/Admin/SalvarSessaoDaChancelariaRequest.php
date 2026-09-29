<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\ClasseSessao;
use App\Models\Evento;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Cadastro/edição de sessões da Loja pela área da Chancelaria.
 *
 * Só aceita os campos que fazem sentido para uma sessão. "tipo",
 * "visibilidade" e "status" NÃO são aceitos do request: são definidos pelo
 * controller, para que o Chanceler nunca consiga transformar a sessão em
 * evento comum nem torná-la pública.
 */
final class SalvarSessaoDaChancelariaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $evento = $this->route('evento');

        return $evento instanceof Evento
            ? $this->user()?->can('chancelaria.editar') === true
            : $this->user()?->can('chancelaria.criar') === true;
    }

    public function rules(): array
    {
        return [
            'titulo' => ['nullable', 'string', 'max:255'],
            'inicio_em' => ['required', 'date'],
            'sessao_classe' => ['nullable', Rule::enum(ClasseSessao::class)],
            'local' => ['nullable', 'string', 'max:255'],
            // Ação do formulário: apenas salvar ou já seguir para o
            // lançamento de frequência.
            'acao' => ['nullable', 'in:salvar,frequencia'],
        ];
    }

    public function messages(): array
    {
        return [
            'inicio_em.required' => 'Informe a data e o horário da sessão.',
        ];
    }
}
