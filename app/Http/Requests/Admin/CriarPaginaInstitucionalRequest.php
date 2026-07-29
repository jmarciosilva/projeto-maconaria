<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Support\NormalizadorTexto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

final class CriarPaginaInstitucionalRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $dados = $this->normalizarTextosUtf8([
            'slug',
            'titulo',
            'conteudo',
            'meta_titulo',
            'meta_descricao',
        ]);

        if (! empty($dados['titulo']) && blank($dados['slug'] ?? null)) {
            $dados['slug'] = Str::slug($dados['titulo']);
        } elseif (! empty($dados['slug'])) {
            $dados['slug'] = Str::slug($dados['slug']);
        }

        $this->merge($dados);
    }

    public function authorize(): bool
    {
        return $this->user()?->can('cms.editar') === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'slug' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9]+(-[a-z0-9]+)*$/', Rule::unique('paginas_institucionais', 'slug')],
            'titulo' => ['required', 'string', 'max:255'],
            'conteudo' => ['nullable', 'string'],
            'meta_titulo' => ['nullable', 'string', 'max:255'],
            'meta_descricao' => ['nullable', 'string', 'max:255'],
            'publicado' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slug.required' => 'Informe o slug (identificador da URL).',
            'slug.regex' => 'O slug deve conter apenas letras minúsculas, números e hífens (ex.: sobre-nos).',
            'slug.unique' => 'Já existe uma página com este slug.',
            'titulo.required' => 'Informe o título da página.',
        ];
    }

    /**
     * @param  array<int, string>  $campos
     * @return array<string, string|null>
     */
    private function normalizarTextosUtf8(array $campos): array
    {
        $normalizados = [];

        foreach ($campos as $campo) {
            $valor = $this->input($campo);

            if (! is_string($valor)) {
                continue;
            }

            $normalizados[$campo] = NormalizadorTexto::paraUtf8($valor);
        }

        return $normalizados;
    }
}
