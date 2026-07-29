<?php

use App\Http\Controllers\Admin\CarrosselItemController;
use App\Http\Controllers\Admin\ConfiguracaoInstitucionalController;
use App\Http\Controllers\Admin\IrmaoController;
use App\Http\Controllers\Admin\PerfilController;
use App\Http\Controllers\Admin\UsuarioController;
use Illuminate\Support\Facades\Route;

// As rotas do painel administrativo (usuários, perfis, permissões, CMS etc.)
// são adicionadas incrementalmente conforme cada módulo é implementado.
//
// A exigência de e-mail verificado ("verified") está temporariamente
// desativada aqui pelo mesmo motivo de routes/web.php: sem SMTP configurado
// (Fase 11), o usuário nunca receberia o link de verificação.
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
    Route::get('/usuarios/novo', [UsuarioController::class, 'create'])->name('usuarios.create');
    Route::post('/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
    Route::get('/usuarios/{usuario}/editar', [UsuarioController::class, 'edit'])->name('usuarios.edit');
    Route::put('/usuarios/{usuario}', [UsuarioController::class, 'update'])->name('usuarios.update');
    Route::patch('/usuarios/{usuario}/ativar', [UsuarioController::class, 'ativar'])->name('usuarios.ativar');
    Route::patch('/usuarios/{usuario}/desativar', [UsuarioController::class, 'desativar'])->name('usuarios.desativar');
    Route::patch('/usuarios/{usuario}/bloquear', [UsuarioController::class, 'bloquear'])->name('usuarios.bloquear');
    Route::patch('/usuarios/{usuario}/desbloquear', [UsuarioController::class, 'desbloquear'])->name('usuarios.desbloquear');

    Route::get('/perfis', [PerfilController::class, 'index'])->name('perfis.index');

    Route::get('/irmaos', [IrmaoController::class, 'index'])->name('irmaos.index');
    Route::get('/irmaos/novo', [IrmaoController::class, 'create'])->name('irmaos.create');
    Route::post('/irmaos', [IrmaoController::class, 'store'])->name('irmaos.store');
    Route::get('/irmaos/{irmao}', [IrmaoController::class, 'show'])->name('irmaos.show');
    Route::get('/irmaos/{irmao}/editar', [IrmaoController::class, 'edit'])->name('irmaos.edit');
    Route::put('/irmaos/{irmao}', [IrmaoController::class, 'update'])->name('irmaos.update');
    Route::delete('/irmaos/{irmao}', [IrmaoController::class, 'destroy'])->name('irmaos.destroy');
    Route::get('/irmaos/{irmao}/foto', [IrmaoController::class, 'foto'])->name('irmaos.foto');

    Route::get('/configuracoes/institucional', [ConfiguracaoInstitucionalController::class, 'edit'])->name('configuracoes.institucional.edit');
    Route::put('/configuracoes/institucional', [ConfiguracaoInstitucionalController::class, 'update'])->name('configuracoes.institucional.update');

    Route::get('/carrossel', [CarrosselItemController::class, 'index'])->name('carrossel.index');
    Route::get('/carrossel/novo', [CarrosselItemController::class, 'create'])->name('carrossel.create');
    Route::post('/carrossel', [CarrosselItemController::class, 'store'])->name('carrossel.store');
    Route::get('/carrossel/{carrossel}/editar', [CarrosselItemController::class, 'edit'])->name('carrossel.edit');
    Route::put('/carrossel/{carrossel}', [CarrosselItemController::class, 'update'])->name('carrossel.update');
    Route::delete('/carrossel/{carrossel}', [CarrosselItemController::class, 'destroy'])->name('carrossel.destroy');
});
