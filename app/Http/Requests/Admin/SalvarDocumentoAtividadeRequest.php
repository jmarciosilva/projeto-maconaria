<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\StatusDocumentoTrabalho;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SalvarDocumentoAtividadeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('documentos.avaliar') === true;
    }

    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::enum(StatusDocumentoTrabalho::class)],
            'prazo_entrega_em' => ['nullable', 'date'],
            'arquivos' => ['nullable', 'array'],
            'arquivos.*' => ['file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx'],
        ];
    }
}
