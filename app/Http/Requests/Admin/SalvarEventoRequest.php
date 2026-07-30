<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\StatusEvento;
use App\Enums\TipoEvento;
use App\Enums\VisibilidadeEvento;
use App\Models\Evento;
use App\Support\NormalizadorTexto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

final class SalvarEventoRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $dados = $this->normalizarTextosUtf8(['titulo', 'slug', 'descricao', 'local']);

        if (! empty($dados['titulo']) && blank($dados['slug'] ?? null)) {
            $dados['slug'] = Str::slug($dados['titulo']);
        } elseif (! empty($dados['slug'])) {
            $dados['slug'] = Str::slug($dados['slug']);
        }

        $dados['permite_confirmacao'] = $this->boolean('permite_confirmacao');
        $dados['capacidade'] = $this->input('capacidade') === '' ? null : $this->input('capacidade');

        $this->merge($dados);
    }

    public function authorize(): bool
    {
        $permissao = $this->isMethod('post') ? 'eventos.criar' : 'eventos.editar';

        return $this->user()?->can($permissao) === true;
    }

    public function rules(): array
    {
        $evento = $this->route('evento');
        $evento = $evento instanceof Evento ? $evento : null;

        return [
            'titulo' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:140', 'regex:/^[a-z0-9]+(-[a-z0-9]+)*$/', Rule::unique('eventos', 'slug')->ignore($evento)],
            'descricao' => ['nullable', 'string'],
            'imagem_capa' => ['nullable', 'image', 'max:4096'],
            'tipo' => ['required', Rule::enum(TipoEvento::class)],
            'status' => ['required', Rule::enum(StatusEvento::class)],
            'visibilidade' => ['required', Rule::enum(VisibilidadeEvento::class)],
            'local' => ['nullable', 'string', 'max:255'],
            'inicio_em' => ['required', 'date'],
            'fim_em' => ['nullable', 'date', 'after:inicio_em'],
            'inscricoes_ate' => ['nullable', 'date', 'before_or_equal:inicio_em'],
            'capacidade' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'permite_confirmacao' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'titulo.required' => 'Informe o título do evento.',
            'slug.unique' => 'Já existe um evento com este slug.',
            'fim_em.after' => 'A data de término deve ser posterior ao início.',
            'inscricoes_ate.before_or_equal' => 'O prazo de confirmação deve ser anterior ou igual ao início do evento.',
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
