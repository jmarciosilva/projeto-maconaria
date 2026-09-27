<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Mesma regra de negócio do login web (App\Http\Requests\Auth\LoginRequest):
 * limite de tentativas e bloqueio de usuário inativo/bloqueado. A diferença é
 * a forma de autenticar — aqui não há sessão (Auth::attempt), apenas
 * verificação de credenciais para emitir um token Sanctum.
 */
final class LoginApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'codigo_cim' => ['required', 'string', 'regex:/^\d{6}$/'],
            'password' => ['required', 'string'],
            // Identifica o dispositivo/instalação do app — vira o "nome" do
            // token Sanctum, para o usuário reconhecer e revogar sessões
            // específicas no futuro (ex.: "iPhone de Fulano").
            'device_name' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * @throws ValidationException
     */
    public function autenticar(): User
    {
        $this->ensureIsNotRateLimited();

        $usuario = User::query()->where('codigo_cim', $this->string('codigo_cim')->value())->first();

        if (! $usuario || ! Hash::check($this->string('password')->value(), $usuario->password)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'codigo_cim' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        // Usuários inativos ou bloqueados nunca recebem um token, mesmo com
        // credenciais corretas — mesma regra do login web.
        if (! $usuario->estaAtivo()) {
            throw ValidationException::withMessages([
                'codigo_cim' => 'Este usuário está inativo ou bloqueado. Procure um administrador.',
            ]);
        }

        $usuario->forceFill(['ultimo_acesso_em' => now()])->save();

        return $usuario;
    }

    /**
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'codigo_cim' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return 'api|'.Str::transliterate(Str::lower($this->string('codigo_cim')->value())).'|'.$this->ip();
    }
}
