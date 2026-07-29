<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

final class UsuarioPolicy
{
    public function viewAny(User $usuario): bool
    {
        return $usuario->can('usuarios.visualizar');
    }

    public function view(User $usuario, User $alvo): bool
    {
        return $usuario->can('usuarios.visualizar');
    }

    public function create(User $usuario): bool
    {
        return $usuario->can('usuarios.criar');
    }

    public function update(User $usuario, User $alvo): bool
    {
        return $usuario->can('usuarios.editar');
    }

    public function delete(User $usuario, User $alvo): bool
    {
        // Um usuário nunca pode excluir/desativar a própria conta por este painel.
        return $usuario->can('usuarios.excluir') && $usuario->isNot($alvo);
    }

    public function atribuirPerfis(User $usuario, User $alvo): bool
    {
        return $usuario->can('usuarios.atribuir-perfis');
    }
}
