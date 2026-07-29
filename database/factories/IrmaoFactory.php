<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SituacaoCadastralIrmao;
use App\Models\Irmao;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Irmao>
 */
final class IrmaoFactory extends Factory
{
    protected $model = Irmao::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nome_completo' => $this->faker->name(),
            'cpf' => $this->cpfValidoUnico(),
            'situacao_cadastral' => SituacaoCadastralIrmao::ATIVO,
        ];
    }

    /**
     * Gera um CPF com dígitos verificadores válidos, usando a sequência do
     * gerador de números únicos do Faker como base para os 9 primeiros dígitos.
     */
    private function cpfValidoUnico(): string
    {
        $base = (string) $this->faker->unique()->numberBetween(100000000, 999999999);
        $digitos = str_split($base);

        for ($posicaoDigitoVerificador = 9; $posicaoDigitoVerificador < 11; $posicaoDigitoVerificador++) {
            $soma = 0;

            for ($posicao = 0; $posicao < $posicaoDigitoVerificador; $posicao++) {
                $soma += (int) $digitos[$posicao] * (($posicaoDigitoVerificador + 1) - $posicao);
            }

            $resto = ($soma * 10) % 11;
            $digitos[] = $resto === 10 ? 0 : $resto;
        }

        return implode('', $digitos);
    }
}
