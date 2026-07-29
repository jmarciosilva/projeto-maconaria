<?php

namespace App\Models;

use App\Enums\StatusUsuario;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'telefone'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected $guard_name = 'web';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => StatusUsuario::class,
            'deve_alterar_senha' => 'boolean',
            'bloqueado_em' => 'datetime',
            'ultimo_acesso_em' => 'datetime',
        ];
    }

    public function estaAtivo(): bool
    {
        return $this->status === StatusUsuario::ATIVO && $this->bloqueado_em === null;
    }

    public function estaBloqueado(): bool
    {
        return $this->bloqueado_em !== null;
    }
}
