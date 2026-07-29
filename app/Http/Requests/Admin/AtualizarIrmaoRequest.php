<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\GrauMaconico;
use App\Enums\SituacaoCadastralIrmao;
use App\Models\User;
use App\Rules\CpfValido;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class AtualizarIrmaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('irmao')) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $irmao = $this->route('irmao');

        return [
            'nome_completo' => ['required', 'string', 'max:255'],
            'nome_social' => ['nullable', 'string', 'max:255'],
            'data_nascimento' => ['nullable', 'date'],
            'cpf' => ['required', 'string', new CpfValido, Rule::unique('irmaos', 'cpf')->ignore($irmao->id)],
            'rg' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefone' => ['nullable', 'string', 'regex:/^\(\d{2}\) \d{5}-\d{4}$/'],
            'endereco' => ['nullable', 'string', 'max:255'],
            'numero' => ['nullable', 'string', 'max:20'],
            'complemento' => ['nullable', 'string', 'max:255'],
            'bairro' => ['nullable', 'string', 'max:255'],
            'cidade' => ['nullable', 'string', 'max:255'],
            'estado' => ['nullable', 'string', 'size:2'],
            'cep' => ['nullable', 'string', 'regex:/^\d{5}-\d{3}$/'],
            'data_iniciacao' => ['nullable', 'date'],
            'data_elevacao' => ['nullable', 'date'],
            'data_exaltacao' => ['nullable', 'date'],
            'cim' => ['nullable', 'string', 'max:50'],
            'grau_atual' => ['nullable', Rule::enum(GrauMaconico::class)],
            'situacao_cadastral' => ['required', Rule::enum(SituacaoCadastralIrmao::class)],
            'cargo_atual' => ['nullable', 'string', 'max:100'],
            'data_ingresso_loja' => ['nullable', 'date'],
            'data_desligamento' => ['nullable', 'date'],
            'observacoes_administrativas' => ['nullable', 'string'],
            'fotografia' => ['nullable', 'image', 'max:4096'],
            'usuario_id' => [
                'nullable',
                Rule::exists('users', 'id'),
                function (string $attribute, mixed $value, Closure $fail) use ($irmao): void {
                    if ($value && User::query()->whereKey($value)->whereNotNull('irmao_id')->where('irmao_id', '!=', $irmao->id)->exists()) {
                        $fail('O usuário selecionado já está vinculado a outro Irmão.');
                    }
                },
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nome_completo.required' => 'Informe o nome completo do Irmão.',
            'cpf.required' => 'Informe o CPF.',
            'cpf.unique' => 'Já existe um Irmão cadastrado com este CPF.',
            'situacao_cadastral.required' => 'Informe a situação cadastral.',
            'fotografia.image' => 'A fotografia deve ser um arquivo de imagem.',
            'fotografia.max' => 'A fotografia não pode ultrapassar 4 MB.',
            'telefone.regex' => 'Informe o telefone no formato (00) 00000-0000.',
            'cep.regex' => 'Informe o CEP no formato 00000-000.',
        ];
    }
}
