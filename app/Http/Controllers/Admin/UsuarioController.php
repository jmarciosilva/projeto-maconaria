<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\StatusUsuario;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AtualizarUsuarioRequest;
use App\Http\Requests\Admin\CriarUsuarioRequest;
use App\Models\User;
use App\Support\RegistradorDeAuditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

final class UsuarioController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        $usuarios = User::query()
            ->with('roles')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.usuarios.index', compact('usuarios'));
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        $perfis = Role::orderBy('name')->pluck('name', 'name');

        return view('admin.usuarios.create', compact('perfis'));
    }

    public function store(CriarUsuarioRequest $request): RedirectResponse
    {
        $dados = $request->validated();

        $usuario = User::create([
            'name' => $dados['name'],
            'email' => $dados['email'],
            'telefone' => $dados['telefone'] ?? null,
            'password' => Hash::make($dados['password']),
            'status' => StatusUsuario::ATIVO,
            'deve_alterar_senha' => $dados['deve_alterar_senha'] ?? true,
        ]);

        $usuario->syncRoles($dados['perfis'] ?? []);

        RegistradorDeAuditoria::registrar(
            acao: 'criar',
            modulo: 'usuarios',
            entidade: 'User',
            entidadeId: $usuario->id,
            dadosNovos: ['name' => $usuario->name, 'email' => $usuario->email, 'perfis' => $dados['perfis'] ?? []],
        );

        return redirect()
            ->route('admin.usuarios.index')
            ->with('sucesso', 'Usuário cadastrado com sucesso.');
    }

    public function edit(User $usuario): View
    {
        $this->authorize('update', $usuario);

        $perfis = Role::orderBy('name')->pluck('name', 'name');
        $perfisDoUsuario = $usuario->roles->pluck('name')->all();

        return view('admin.usuarios.edit', compact('usuario', 'perfis', 'perfisDoUsuario'));
    }

    public function update(AtualizarUsuarioRequest $request, User $usuario): RedirectResponse
    {
        $dados = $request->validated();

        $anterior = ['name' => $usuario->name, 'email' => $usuario->email];

        $usuario->fill([
            'name' => $dados['name'],
            'email' => $dados['email'],
            'telefone' => $dados['telefone'] ?? null,
            'deve_alterar_senha' => $dados['deve_alterar_senha'] ?? false,
        ])->save();

        $usuario->syncRoles($dados['perfis'] ?? []);

        RegistradorDeAuditoria::registrar(
            acao: 'editar',
            modulo: 'usuarios',
            entidade: 'User',
            entidadeId: $usuario->id,
            dadosAnteriores: $anterior,
            dadosNovos: ['name' => $usuario->name, 'email' => $usuario->email, 'perfis' => $dados['perfis'] ?? []],
        );

        return redirect()
            ->route('admin.usuarios.index')
            ->with('sucesso', 'Usuário atualizado com sucesso.');
    }

    public function ativar(User $usuario): RedirectResponse
    {
        $this->authorize('update', $usuario);

        $usuario->forceFill(['status' => StatusUsuario::ATIVO])->save();

        RegistradorDeAuditoria::registrar('ativar', 'usuarios', 'User', $usuario->id);

        return back()->with('sucesso', 'Usuário ativado com sucesso.');
    }

    public function desativar(User $usuario): RedirectResponse
    {
        $this->authorize('delete', $usuario);

        $usuario->forceFill(['status' => StatusUsuario::INATIVO])->save();

        RegistradorDeAuditoria::registrar('desativar', 'usuarios', 'User', $usuario->id);

        return back()->with('sucesso', 'Usuário desativado com sucesso.');
    }

    public function bloquear(User $usuario): RedirectResponse
    {
        $this->authorize('delete', $usuario);

        $usuario->forceFill(['bloqueado_em' => now()])->save();

        RegistradorDeAuditoria::registrar('bloquear', 'usuarios', 'User', $usuario->id);

        return back()->with('sucesso', 'Usuário bloqueado com sucesso.');
    }

    public function desbloquear(User $usuario): RedirectResponse
    {
        $this->authorize('update', $usuario);

        $usuario->forceFill(['bloqueado_em' => null])->save();

        RegistradorDeAuditoria::registrar('desbloquear', 'usuarios', 'User', $usuario->id);

        return back()->with('sucesso', 'Usuário desbloqueado com sucesso.');
    }
}
