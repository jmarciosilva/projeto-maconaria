<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

final class SalvarDocumentoEntregaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('documentos.enviar') === true;
    }

    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string', 'max:5000'],
            'arquivos' => ['required', 'array', 'min:1'],
            'arquivos.*' => ['file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx'],
        ];
    }
}
