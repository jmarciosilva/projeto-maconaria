<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\StatusFrequencia;
use App\Enums\StatusLancamentoFinanceiro;
use App\Enums\StatusUsuario;
use App\Enums\TipoLancamentoFinanceiro;
use App\Http\Controllers\Controller;
use App\Models\ChancelariaFrequencia;
use App\Models\ChancelariaVisitante;
use App\Models\Evento;
use App\Models\MuralComentario;
use App\Models\Noticia;
use App\Models\TesourariaLancamento;
use App\Models\User;
use Illuminate\View\View;

final class DashboardController extends Controller
{
    public function index(): View
    {
        $usuario = auth()->user();
        $dados = [];

        if ($usuario->can('usuarios.visualizar')) {
            $dados['usuarios'] = [
                'total' => User::query()->count(),
                'ativos' => User::query()->where('status', StatusUsuario::ATIVO->value)->whereNull('bloqueado_em')->count(),
                'inativos' => User::query()->where('status', StatusUsuario::INATIVO->value)->count(),
                'bloqueados' => User::query()->whereNotNull('bloqueado_em')->count(),
            ];
        }

        if ($usuario->can('tesouraria.visualizar')) {
            $receitas = TesourariaLancamento::query()
                ->where('tipo', TipoLancamentoFinanceiro::RECEITA->value)
                ->where('status', StatusLancamentoFinanceiro::BAIXADO->value)
                ->sum('valor_centavos');
            $despesas = TesourariaLancamento::query()
                ->where('tipo', TipoLancamentoFinanceiro::DESPESA->value)
                ->where('status', StatusLancamentoFinanceiro::BAIXADO->value)
                ->sum('valor_centavos');

            $dados['tesouraria'] = [
                'receitas' => $receitas,
                'despesas' => $despesas,
                'saldo' => $receitas - $despesas,
                'pendentes' => TesourariaLancamento::query()->where('status', StatusLancamentoFinanceiro::PENDENTE->value)->count(),
            ];
        }

        if ($usuario->can('chancelaria.visualizar')) {
            $inicio = now()->subMonths(3)->startOfDay();

            $dados['chancelaria'] = [
                'presencas' => ChancelariaFrequencia::query()->presentes()->where('created_at', '>=', $inicio)->count(),
                'ausencias' => ChancelariaFrequencia::query()->ausencias()->where('created_at', '>=', $inicio)->count(),
                'visitantes' => ChancelariaVisitante::query()->where('created_at', '>=', $inicio)->count(),
            ];
        }

        if ($usuario->can('noticias.visualizar') || $usuario->can('eventos.visualizar') || $usuario->can('mural.visualizar')) {
            $dados['conteudo'] = [
                'noticias' => $usuario->can('noticias.visualizar') ? Noticia::query()->publicaNoSite()->count() : null,
                'eventos' => $usuario->can('eventos.visualizar') ? Evento::query()->futuro()->count() : null,
                'comentariosPendentes' => $usuario->can('mural.visualizar') ? MuralComentario::query()->where('aprovado', false)->count() : null,
            ];
        }

        return view('admin.dashboard', ['dados' => $dados]);
    }
}
