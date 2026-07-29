<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class CpfValido implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $cpf = preg_replace('/\D/', '', (string) $value);

        if (! $this->possuiFormatoValido($cpf) || ! $this->possuiDigitosVerificadoresValidos($cpf)) {
            $fail('O :attribute informado não é um CPF válido.');
        }
    }

    private function possuiFormatoValido(string $cpf): bool
    {
        // CPFs com todos os dígitos iguais (ex.: 00000000000) têm dígitos
        // verificadores matematicamente válidos, mas nunca são documentos reais.
        return strlen($cpf) === 11 && preg_match('/^(\d)\1{10}$/', $cpf) !== 1;
    }

    private function possuiDigitosVerificadoresValidos(string $cpf): bool
    {
        for ($posicaoDigitoVerificador = 9; $posicaoDigitoVerificador < 11; $posicaoDigitoVerificador++) {
            $soma = 0;

            for ($posicao = 0; $posicao < $posicaoDigitoVerificador; $posicao++) {
                $soma += (int) $cpf[$posicao] * (($posicaoDigitoVerificador + 1) - $posicao);
            }

            $resto = ($soma * 10) % 11;
            $digitoEsperado = $resto === 10 ? 0 : $resto;

            if ((int) $cpf[$posicaoDigitoVerificador] !== $digitoEsperado) {
                return false;
            }
        }

        return true;
    }
}
