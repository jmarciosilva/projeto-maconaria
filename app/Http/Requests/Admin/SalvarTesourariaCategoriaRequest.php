<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\TipoLancamentoFinanceiro;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SalvarTesourariaCategoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('tesouraria.editar') === true;
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:120'],
            'tipo' => ['required', Rule::enum(TipoLancamentoFinanceiro::class)],
            'ativa' => ['boolean'],
        ];
    }
}
