<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\StatusLancamentoFinanceiro;
use App\Enums\TipoLancamentoFinanceiro;
use App\Support\Tesouraria\ConversorMoeda;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SalvarTesourariaLancamentoRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['valor_centavos' => ConversorMoeda::paraCentavos($this->input('valor'))]);
    }

    public function authorize(): bool
    {
        $permissao = $this->isMethod('post') ? 'tesouraria.criar' : 'tesouraria.editar';

        return $this->user()?->can($permissao) === true;
    }

    public function rules(): array
    {
        return [
            'categoria_id' => ['required', Rule::exists('tesouraria_categorias', 'id')],
            'conta_id' => ['required', Rule::exists('tesouraria_contas', 'id')],
            'irmao_id' => ['nullable', Rule::exists('irmaos', 'id')],
            'tipo' => ['required', Rule::enum(TipoLancamentoFinanceiro::class)],
            'status' => ['required', Rule::in([StatusLancamentoFinanceiro::RASCUNHO->value, StatusLancamentoFinanceiro::PENDENTE->value])],
            'descricao' => ['required', 'string', 'max:255'],
            'valor_centavos' => ['required', 'integer', 'min:1'],
            'data_competencia' => ['required', 'date'],
            'data_vencimento' => ['nullable', 'date'],
            'observacao' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
