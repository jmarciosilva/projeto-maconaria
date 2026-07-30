<?php

use App\Http\Controllers\AreaRestrita\EventoController as AreaRestritaEventoController;
use App\Http\Controllers\AreaRestrita\PainelController;
use App\Http\Controllers\AreaRestrita\ProfileController;
use App\Http\Controllers\Site\ContatoController;
use App\Http\Controllers\Site\EventoController as SiteEventoController;
use App\Http\Controllers\Site\GaleriaController;
use App\Http\Controllers\Site\MuralController;
use App\Http\Controllers\Site\MuralInteracaoController;
use App\Http\Controllers\Site\NoticiaController;
use App\Http\Controllers\Site\PaginaInicialController;
use App\Http\Controllers\Site\PaginaInstitucionalController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PaginaInicialController::class, 'index'])->name('home');
Route::get('/noticias', [NoticiaController::class, 'index'])->name('noticias.index');
Route::get('/noticias/{slug}', [NoticiaController::class, 'mostrar'])->name('noticias.mostrar');
Route::get('/eventos', [SiteEventoController::class, 'index'])->name('eventos.index');
Route::get('/eventos/{slug}', [SiteEventoController::class, 'mostrar'])->name('eventos.mostrar');
Route::get('/calendario', [SiteEventoController::class, 'calendario'])->name('calendario');

// Mural e Galeria são sempre públicos para visualização — só interagir
// (comentar/curtir) exige login, ver o grupo "auth" abaixo.
Route::get('/mural', [MuralController::class, 'index'])->name('mural.index');
Route::get('/mural/{publicacao}', [MuralController::class, 'mostrar'])->name('mural.mostrar');
Route::get('/galeria', [GaleriaController::class, 'index'])->name('galeria.index');
Route::get('/galeria/{slug}', [GaleriaController::class, 'mostrar'])->name('galeria.mostrar');

// Páginas institucionais com URL fixa (seção 5/7 do escopo). O slug de cada
// uma corresponde ao registro em "paginas_institucionais" (ver
// PaginaInstitucionalSeeder) — atualizado para os slugs realmente usados
// pela administração da Loja ao cadastrar o conteúdo pelo painel (nem
// sempre idênticos ao valor originalmente previsto no seeder). Uma página
// institucional adicional, criada livremente pelo painel, fica acessível
// pela rota genérica ao final do grupo (/institucional/{slug}).
Route::get('/sobre-nos', [PaginaInstitucionalController::class, 'mostrar'])
    ->defaults('slug', 'sobre-nos')->name('paginas.sobre-nos');
Route::get('/nossa-historia', [PaginaInstitucionalController::class, 'mostrar'])
    ->defaults('slug', 'nossa-historia')->name('paginas.nossa-historia');
Route::get('/maconaria', [PaginaInstitucionalController::class, 'mostrar'])
    ->defaults('slug', 'o-que-e-maconaria')->name('paginas.maconaria');
Route::get('/maconaria-jovens', [PaginaInstitucionalController::class, 'mostrar'])
    ->defaults('slug', 'maconaria-para-jovens')->name('paginas.maconaria-jovens');
Route::get('/mudar-cidadao', [PaginaInstitucionalController::class, 'mostrar'])
    ->defaults('slug', 'mudando-o-cidadao')->name('paginas.mudar-cidadao');
Route::get('/politica-privacidade', [PaginaInstitucionalController::class, 'mostrar'])
    ->defaults('slug', 'politica-privacidade')->name('paginas.politica-privacidade');
Route::get('/termos-de-uso', [PaginaInstitucionalController::class, 'mostrar'])
    ->defaults('slug', 'termos-de-uso')->name('paginas.termos-de-uso');
Route::get('/institucional/{slug}', [PaginaInstitucionalController::class, 'mostrar'])->name('paginas.mostrar');

Route::get('/contato', [ContatoController::class, 'mostrar'])->name('contato.mostrar');
Route::post('/contato', [ContatoController::class, 'enviar'])
    ->middleware('throttle:5,1')
    ->name('contato.enviar');

// A exigência de e-mail verificado ("verified") está temporariamente
// desativada: ainda não há envio real de e-mails configurado (Fase 11 —
// Configurações de e-mail), então o usuário nunca conseguiria receber o
// link de verificação. Reativar o middleware "verified" assim que o SMTP
// estiver configurado (ver docs/MODULOS.md).
Route::middleware(['auth'])->group(function () {
    Route::get('/area-restrita', [PainelController::class, 'index'])->name('area-restrita');
    Route::get('/area-restrita/eventos', [AreaRestritaEventoController::class, 'index'])->name('area-restrita.eventos.index');
    Route::get('/area-restrita/eventos/{evento}', [AreaRestritaEventoController::class, 'mostrar'])->name('area-restrita.eventos.mostrar');
    Route::post('/area-restrita/eventos/{evento}/confirmar', [AreaRestritaEventoController::class, 'confirmar'])->name('area-restrita.eventos.confirmar');
    Route::delete('/area-restrita/eventos/{evento}/confirmar', [AreaRestritaEventoController::class, 'cancelarConfirmacao'])->name('area-restrita.eventos.cancelar-confirmacao');
    Route::post('/mural/{publicacao}/comentarios', [MuralInteracaoController::class, 'comentar'])->name('mural.comentarios.store');
    Route::post('/mural/{publicacao}/reacoes', [MuralInteracaoController::class, 'reagir'])->name('mural.reacoes.store');

    Route::get('/area-restrita/perfil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/area-restrita/perfil', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/area-restrita/perfil', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

require __DIR__.'/admin.php';
