<?php

use App\Http\Controllers\AreaRestrita\PainelController;
use App\Http\Controllers\AreaRestrita\ProfileController;
use App\Http\Controllers\Site\PaginaInicialController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PaginaInicialController::class, 'index'])->name('home');

// A exigência de e-mail verificado ("verified") está temporariamente
// desativada: ainda não há envio real de e-mails configurado (Fase 11 —
// Configurações de e-mail), então o usuário nunca conseguiria receber o
// link de verificação. Reativar o middleware "verified" assim que o SMTP
// estiver configurado (ver docs/MODULOS.md).
Route::middleware(['auth'])->group(function () {
    Route::get('/area-restrita', [PainelController::class, 'index'])->name('area-restrita');

    Route::get('/area-restrita/perfil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/area-restrita/perfil', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/area-restrita/perfil', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

require __DIR__.'/admin.php';
