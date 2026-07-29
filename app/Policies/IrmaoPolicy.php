<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Irmao;
use App\Models\User;

final class IrmaoPolicy
{
    public function viewAny(User $usuario): bool
    {
        return $usuario->can('irmaos.visualizar');
    }

    public function view(User $usuario, Irmao $irmao): bool
    {
        return $usuario->can('irmaos.visualizar');
    }

    public function create(User $usuario): bool
    {
        return $usuario->can('irmaos.criar');
    }

    public function update(User $usuario, Irmao $irmao): bool
    {
        return $usuario->can('irmaos.editar');
    }

    public function delete(User $usuario, Irmao $irmao): bool
    {
        return $usuario->can('irmaos.excluir');
    }
}
