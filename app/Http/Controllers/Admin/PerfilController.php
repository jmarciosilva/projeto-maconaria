<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

/**
 * Por ora, apenas consulta os perfis e permissões seguidos. A criação e
 * edição de perfis pelo painel (CRUD completo de Role/Permission) fica
 * registrada como pendência em docs/MODULOS.md para uma fase futura.
 */
final class PerfilController extends Controller
{
    public function index(): View
    {
        $this->authorize('perfis.visualizar');

        $perfis = Role::with('permissions')->orderBy('name')->get();

        return view('admin.perfis.index', compact('perfis'));
    }
}
