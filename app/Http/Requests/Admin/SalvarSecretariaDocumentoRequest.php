<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\StatusDocumentoSecretaria;
use App\Enums\TipoDocumentoSecretaria;
use App\Support\NormalizadorTexto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SalvarSecretariaDocumentoRequest extends FormRequest
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
        $permissao = $this->isMethod('post') ? 'secretaria.criar-ata' : 'secretaria.editar-ata';

        return $this->user()?->can($permissao) === true;
    }

    public function rules(): array
    {
        return [
            'tipo' => ['required', Rule::enum(TipoDocumentoSecretaria::class)],
            'titulo' => ['required', 'string', 'max:255'],
            'conteudo' => ['nullable', 'string'],
            'status' => ['required', Rule::in([StatusDocumentoSecretaria::RASCUNHO->value, StatusDocumentoSecretaria::EM_APROVACAO->value])],
            'data_documento' => ['nullable', 'date'],
            'arquivos' => ['array'],
            'arquivos.*' => [
                'file',
                'max:10240',
                'mimes:pdf,doc,docx,xls,xlsx',
                'mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'titulo.required' => 'Informe o título do documento.',
            'status.in' => 'Documentos só podem ser salvos como rascunho ou enviados para aprovação pelo formulário.',
            'arquivos.*.mimes' => 'Os anexos devem ser arquivos PDF, DOC, DOCX, XLS ou XLSX.',
            'arquivos.*.max' => 'Cada anexo deve ter no máximo 10 MB.',
        ];
    }
}
