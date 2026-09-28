<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\StatusNoticia;
use App\Enums\VisibilidadeNoticia;
use App\Support\NormalizadorTexto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class CriarNoticiaRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $dados = $this->normalizarTextosUtf8(['titulo', 'slug', 'resumo', 'conteudo']);

        if (! empty($dados['titulo']) && blank($dados['slug'] ?? null)) {
            $dados['slug'] = Str::slug($dados['titulo']);
        } elseif (! empty($dados['slug'])) {
            $dados['slug'] = Str::slug($dados['slug']);
        }

        $this->merge($dados);
    }

    public function authorize(): bool
    {
        return $this->user()?->can('noticias.criar') === true;
    }

    public function rules(): array
    {
        return [
            'categoria_id' => ['nullable', 'integer', Rule::exists('noticia_categorias', 'id')],
            'tags' => ['array'],
            'tags.*' => ['integer', Rule::exists('noticia_tags', 'id')],
            'titulo' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:140', 'regex:/^[a-z0-9]+(-[a-z0-9]+)*$/', Rule::unique('noticias', 'slug')],
            'resumo' => ['nullable', 'string', 'max:500'],
            'conteudo' => ['nullable', 'string'],
            'imagem_capa' => ['nullable', 'image', 'max:4096'],
            'fotos' => ['nullable', 'array', 'max:50'],
            'fotos.*' => ['image', 'max:4096'],
            'fotos_descricao' => ['nullable', 'array'],
            'fotos_descricao.*' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::enum(StatusNoticia::class)],
            'visibilidade' => ['required', Rule::enum(VisibilidadeNoticia::class)],
            'destaque' => ['boolean'],
            'publicado_em' => ['nullable', 'date'],
            'agendado_para' => ['nullable', 'date', 'required_if:status,'.StatusNoticia::AGENDADA->value],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $status = $this->input('status');
            $conteudo = strip_tags($this->input('conteudo', ''));

            if (
                in_array($status, [StatusNoticia::PUBLICADA->value, StatusNoticia::AGENDADA->value], true)
                && $this->user()?->can('noticias.publicar') !== true
            ) {
                $validator->errors()->add('status', 'Você não possui permissão para publicar ou agendar notícias.');
            }

            if (empty(trim($conteudo)) && $status === StatusNoticia::PUBLICADA->value) {
                $validator->errors()->add('conteudo', 'O conteúdo é obrigatório para publicar uma notícia.');
            }

            if ($status === StatusNoticia::AGENDADA->value && blank($this->input('agendado_para'))) {
                $validator->errors()->add('agendado_para', 'Selecione uma data para agendar a notícia.');
            }

            // Validar total de fotos
            $fotosNovas = count($this->file('fotos', []));
            if ($fotosNovas > 50) {
                $validator->errors()->add('fotos', 'Uma notícia pode possuir no máximo 50 fotos.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'titulo.required' => 'Informe o título da notícia.',
            'slug.required' => 'Informe o slug da notícia.',
            'slug.regex' => 'O slug deve conter apenas letras minúsculas, números e hífens.',
            'slug.unique' => 'Já existe uma notícia com este slug.',
            'agendado_para.required_if' => 'Informe a data de agendamento da notícia.',
            'fotos.max' => 'Máximo de 10 fotos permitidas.',
            'fotos.*.image' => 'Todos os arquivos devem ser imagens válidas.',
            'fotos.*.max' => 'Cada foto deve ter no máximo 4 MB.',
        ];
    }

    private function normalizarTextosUtf8(array $campos): array
    {
        $normalizados = [];

        foreach ($campos as $campo) {
            $valor = $this->input($campo);

            if (is_string($valor)) {
                $normalizados[$campo] = NormalizadorTexto::paraUtf8($valor);
            }
        }

        return $normalizados;
    }
}
