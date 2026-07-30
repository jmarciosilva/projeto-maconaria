<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DocumentoController;
use App\Http\Controllers\Api\V1\EventoController;
use App\Http\Controllers\Api\V1\MuralController;
use App\Http\Controllers\Api\V1\NoticiaController;
use App\Http\Controllers\Api\V1\PerfilController;
use Illuminate\Support\Facades\Route;

// Rotas da API (Fase 12), consumida pelo futuro aplicativo Flutter.
// Autenticação via token (Laravel Sanctum), nunca sessão/cookies — ver
// docs/API-FUTURA.md. Cada módulo reaproveita as mesmas regras de
// visibilidade/autorização já usadas na web (Site, AreaRestrita, Admin).
Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login');
    Route::middleware('auth:sanctum')->post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');

    Route::middleware('auth:sanctum')->get('/perfil', [PerfilController::class, 'show'])->name('perfil.show');

    // Notícias e mural: mesma visibilidade do site público (só conteúdo
    // publicado/público) independentemente de autenticação — ver
    // NoticiaController e MuralController para a suposição registrada.
    Route::get('/noticias', [NoticiaController::class, 'index'])->name('noticias.index');
    Route::get('/noticias/{slug}', [NoticiaController::class, 'show'])->name('noticias.show');

    // Eventos: visibilidade pública para visitantes e pública+restrita para
    // usuários autenticados (mesma regra da área restrita) — por isso as
    // rotas de leitura não exigem token, mas reconhecem um quando enviado.
    Route::get('/eventos', [EventoController::class, 'index'])->name('eventos.index');
    Route::get('/calendario', [EventoController::class, 'calendario'])->name('calendario');
    Route::get('/eventos/{slug}', [EventoController::class, 'show'])->name('eventos.show');
    Route::middleware('auth:sanctum')->post('/eventos/{evento}/confirmar', [EventoController::class, 'confirmar'])->name('eventos.confirmar');
    Route::middleware('auth:sanctum')->delete('/eventos/{evento}/confirmar', [EventoController::class, 'cancelarConfirmacao'])->name('eventos.cancelar-confirmacao');

    Route::get('/mural', [MuralController::class, 'index'])->name('mural.index');
    Route::get('/mural/{publicacao}', [MuralController::class, 'show'])->name('mural.show');
    Route::middleware('auth:sanctum')->post('/mural/{publicacao}/comentarios', [MuralController::class, 'comentar'])->name('mural.comentarios.store');
    Route::middleware('auth:sanctum')->post('/mural/{publicacao}/reacoes', [MuralController::class, 'reagir'])->name('mural.reacoes.store');

    // Documentos e trabalhos: sem conteúdo público, todas as rotas exigem token.
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/documentos', [DocumentoController::class, 'index'])->name('documentos.index');
        Route::get('/documentos/{atividade}', [DocumentoController::class, 'show'])->name('documentos.show');
        Route::post('/documentos/{atividade}/entregas', [DocumentoController::class, 'enviarEntrega'])->name('documentos.entregas.store');
        Route::get('/documentos/arquivos/{arquivo}', [DocumentoController::class, 'baixarArquivo'])->name('documentos.arquivos.baixar');
    });
});
