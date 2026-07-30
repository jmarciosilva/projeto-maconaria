<?php

use App\Http\Controllers\Admin\CarrosselItemController;
use App\Http\Controllers\Admin\ChancelariaComunicadoController;
use App\Http\Controllers\Admin\ChancelariaController;
use App\Http\Controllers\Admin\ChancelariaFrequenciaController;
use App\Http\Controllers\Admin\ChancelariaVisitanteController;
use App\Http\Controllers\Admin\ConfiguracaoInstitucionalController;
use App\Http\Controllers\Admin\EventoController;
use App\Http\Controllers\Admin\IrmaoController;
use App\Http\Controllers\Admin\NoticiaCategoriaController;
use App\Http\Controllers\Admin\NoticiaController;
use App\Http\Controllers\Admin\NoticiaTagController;
use App\Http\Controllers\Admin\PaginaInstitucionalController;
use App\Http\Controllers\Admin\PerfilController;
use App\Http\Controllers\Admin\SecretariaDocumentoController;
use App\Http\Controllers\Admin\TesourariaCategoriaController;
use App\Http\Controllers\Admin\TesourariaContaController;
use App\Http\Controllers\Admin\TesourariaController;
use App\Http\Controllers\Admin\TesourariaFechamentoController;
use App\Http\Controllers\Admin\TesourariaLancamentoController;
use App\Http\Controllers\Admin\TesourariaMensalidadeController;
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

    Route::get('/paginas-institucionais', [PaginaInstitucionalController::class, 'index'])->name('paginas-institucionais.index');
    Route::get('/paginas-institucionais/nova', [PaginaInstitucionalController::class, 'create'])->name('paginas-institucionais.create');
    Route::post('/paginas-institucionais', [PaginaInstitucionalController::class, 'store'])->name('paginas-institucionais.store');
    Route::get('/paginas-institucionais/{pagina}/editar', [PaginaInstitucionalController::class, 'edit'])->name('paginas-institucionais.edit');
    Route::put('/paginas-institucionais/{pagina}', [PaginaInstitucionalController::class, 'update'])->name('paginas-institucionais.update');
    Route::delete('/paginas-institucionais/{pagina}', [PaginaInstitucionalController::class, 'destroy'])->name('paginas-institucionais.destroy');

    Route::get('/noticias/categorias', [NoticiaCategoriaController::class, 'index'])->name('noticia-categorias.index');
    Route::post('/noticias/categorias', [NoticiaCategoriaController::class, 'store'])->name('noticia-categorias.store');
    Route::put('/noticias/categorias/{categoria}', [NoticiaCategoriaController::class, 'update'])->name('noticia-categorias.update');
    Route::delete('/noticias/categorias/{categoria}', [NoticiaCategoriaController::class, 'destroy'])->name('noticia-categorias.destroy');

    Route::get('/noticias/tags', [NoticiaTagController::class, 'index'])->name('noticia-tags.index');
    Route::post('/noticias/tags', [NoticiaTagController::class, 'store'])->name('noticia-tags.store');
    Route::put('/noticias/tags/{tag}', [NoticiaTagController::class, 'update'])->name('noticia-tags.update');
    Route::delete('/noticias/tags/{tag}', [NoticiaTagController::class, 'destroy'])->name('noticia-tags.destroy');

    Route::get('/noticias', [NoticiaController::class, 'index'])->name('noticias.index');
    Route::get('/noticias/nova', [NoticiaController::class, 'create'])->name('noticias.create');
    Route::post('/noticias', [NoticiaController::class, 'store'])->name('noticias.store');
    Route::get('/noticias/{noticia}/editar', [NoticiaController::class, 'edit'])->name('noticias.edit');
    Route::put('/noticias/{noticia}', [NoticiaController::class, 'update'])->name('noticias.update');
    Route::delete('/noticias/{noticia}', [NoticiaController::class, 'destroy'])->name('noticias.destroy');

    Route::get('/eventos/calendario', [EventoController::class, 'calendario'])->name('eventos.calendario');
    Route::get('/eventos', [EventoController::class, 'index'])->name('eventos.index');
    Route::get('/eventos/novo', [EventoController::class, 'create'])->name('eventos.create');
    Route::post('/eventos', [EventoController::class, 'store'])->name('eventos.store');
    Route::get('/eventos/{evento}/editar', [EventoController::class, 'edit'])->name('eventos.edit');
    Route::put('/eventos/{evento}', [EventoController::class, 'update'])->name('eventos.update');
    Route::delete('/eventos/{evento}', [EventoController::class, 'destroy'])->name('eventos.destroy');

    Route::get('/secretaria/documentos', [SecretariaDocumentoController::class, 'index'])->name('secretaria.documentos.index');
    Route::get('/secretaria/documentos/novo', [SecretariaDocumentoController::class, 'create'])->name('secretaria.documentos.create');
    Route::post('/secretaria/documentos', [SecretariaDocumentoController::class, 'store'])->name('secretaria.documentos.store');
    Route::get('/secretaria/documentos/{documento}/editar', [SecretariaDocumentoController::class, 'edit'])->name('secretaria.documentos.edit');
    Route::put('/secretaria/documentos/{documento}', [SecretariaDocumentoController::class, 'update'])->name('secretaria.documentos.update');
    Route::patch('/secretaria/documentos/{documento}/aprovar', [SecretariaDocumentoController::class, 'aprovar'])->name('secretaria.documentos.aprovar');
    Route::patch('/secretaria/documentos/{documento}/publicar', [SecretariaDocumentoController::class, 'publicar'])->name('secretaria.documentos.publicar');
    Route::get('/secretaria/documentos/{documento}/arquivos/{arquivo}', [SecretariaDocumentoController::class, 'baixarArquivo'])->name('secretaria.documentos.arquivos.baixar');
    Route::delete('/secretaria/documentos/{documento}/arquivos/{arquivo}', [SecretariaDocumentoController::class, 'removerArquivo'])->name('secretaria.documentos.arquivos.destroy');
    Route::delete('/secretaria/documentos/{documento}', [SecretariaDocumentoController::class, 'destroy'])->name('secretaria.documentos.destroy');

    Route::get('/chancelaria', [ChancelariaController::class, 'index'])->name('chancelaria.index');
    Route::get('/chancelaria/frequencias', [ChancelariaFrequenciaController::class, 'selecionarEvento'])->name('chancelaria.frequencias.selecionar-evento');
    Route::get('/chancelaria/frequencias/{evento}', [ChancelariaFrequenciaController::class, 'edit'])->name('chancelaria.frequencias.edit');
    Route::put('/chancelaria/frequencias/{evento}', [ChancelariaFrequenciaController::class, 'update'])->name('chancelaria.frequencias.update');
    Route::resource('/chancelaria/visitantes', ChancelariaVisitanteController::class)
        ->except(['show'])
        ->names('chancelaria.visitantes');
    Route::resource('/chancelaria/comunicados', ChancelariaComunicadoController::class)
        ->except(['show'])
        ->names('chancelaria.comunicados');

    Route::get('/tesouraria', [TesourariaController::class, 'index'])->name('tesouraria.index');
    Route::get('/tesouraria/categorias', [TesourariaCategoriaController::class, 'index'])->name('tesouraria.categorias.index');
    Route::post('/tesouraria/categorias', [TesourariaCategoriaController::class, 'store'])->name('tesouraria.categorias.store');
    Route::get('/tesouraria/contas', [TesourariaContaController::class, 'index'])->name('tesouraria.contas.index');
    Route::post('/tesouraria/contas', [TesourariaContaController::class, 'store'])->name('tesouraria.contas.store');
    Route::get('/tesouraria/lancamentos', [TesourariaLancamentoController::class, 'index'])->name('tesouraria.lancamentos.index');
    Route::get('/tesouraria/lancamentos/novo', [TesourariaLancamentoController::class, 'create'])->name('tesouraria.lancamentos.create');
    Route::post('/tesouraria/lancamentos', [TesourariaLancamentoController::class, 'store'])->name('tesouraria.lancamentos.store');
    Route::get('/tesouraria/lancamentos/{lancamento}/editar', [TesourariaLancamentoController::class, 'edit'])->name('tesouraria.lancamentos.edit');
    Route::put('/tesouraria/lancamentos/{lancamento}', [TesourariaLancamentoController::class, 'update'])->name('tesouraria.lancamentos.update');
    Route::patch('/tesouraria/lancamentos/{lancamento}/aprovar', [TesourariaLancamentoController::class, 'aprovar'])->name('tesouraria.lancamentos.aprovar');
    Route::patch('/tesouraria/lancamentos/{lancamento}/baixar', [TesourariaLancamentoController::class, 'baixar'])->name('tesouraria.lancamentos.baixar');
    Route::get('/tesouraria/mensalidades/nova', [TesourariaMensalidadeController::class, 'create'])->name('tesouraria.mensalidades.create');
    Route::post('/tesouraria/mensalidades', [TesourariaMensalidadeController::class, 'store'])->name('tesouraria.mensalidades.store');
    Route::get('/tesouraria/fechamentos', [TesourariaFechamentoController::class, 'index'])->name('tesouraria.fechamentos.index');
    Route::post('/tesouraria/fechamentos', [TesourariaFechamentoController::class, 'store'])->name('tesouraria.fechamentos.store');
});
