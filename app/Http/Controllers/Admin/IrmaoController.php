<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\TipoHistoricoIrmao;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AtualizarIrmaoRequest;
use App\Http\Requests\Admin\CriarIrmaoRequest;
use App\Models\Irmao;
use App\Models\User;
use App\Support\RegistradorDeAuditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class IrmaoController extends Controller
{
    /**
     * Campos cuja alteração gera um registro no histórico do Irmão.
     *
     * @var array<string, TipoHistoricoIrmao>
     */
    private const CAMPOS_HISTORIADOS = [
        'grau_atual' => TipoHistoricoIrmao::GRAU,
        'situacao_cadastral' => TipoHistoricoIrmao::SITUACAO_CADASTRAL,
        'cargo_atual' => TipoHistoricoIrmao::CARGO,
    ];

    public function index(): View
    {
        $this->authorize('viewAny', Irmao::class);

        $irmaos = Irmao::query()
            ->when(request('nome'), fn ($query, $nome) => $query->where('nome_completo', 'like', "%{$nome}%"))
            ->when(request('situacao'), fn ($query, $situacao) => $query->where('situacao_cadastral', $situacao))
            ->when(request('grau'), fn ($query, $grau) => $query->where('grau_atual', $grau))
            ->orderBy('nome_completo')
            ->paginate(15)
            ->withQueryString();

        return view('admin.irmaos.index', compact('irmaos'));
    }

    public function create(): View
    {
        $this->authorize('create', Irmao::class);

        $usuariosDisponiveis = User::query()->whereNull('irmao_id')->orderBy('name')->pluck('name', 'id');

        return view('admin.irmaos.create', compact('usuariosDisponiveis'));
    }

    public function store(CriarIrmaoRequest $request): RedirectResponse
    {
        $dados = $request->validated();

        $irmao = Irmao::create(collect($dados)->except(['fotografia', 'usuario_id'])->all());

        if ($request->hasFile('fotografia')) {
            $irmao->update(['fotografia' => $this->armazenarFotografia($request, $irmao)]);
        }

        if (! empty($dados['usuario_id'])) {
            User::whereKey($dados['usuario_id'])->update(['irmao_id' => $irmao->id]);
        }

        $this->registrarHistoricoCadastral($irmao, 'Cadastro inicial do Irmão.');

        RegistradorDeAuditoria::registrar(
            acao: 'criar',
            modulo: 'irmaos',
            entidade: 'Irmao',
            entidadeId: $irmao->id,
            dadosNovos: ['nome_completo' => $irmao->nome_completo],
        );

        return redirect()
            ->route('admin.irmaos.show', $irmao)
            ->with('sucesso', 'Irmão cadastrado com sucesso.');
    }

    public function show(Irmao $irmao): View
    {
        $this->authorize('view', $irmao);

        $irmao->load(['usuario', 'historicos.responsavel']);

        return view('admin.irmaos.show', compact('irmao'));
    }

    public function edit(Irmao $irmao): View
    {
        $this->authorize('update', $irmao);

        $irmao->load('usuario');

        $usuariosDisponiveis = User::query()
            ->where(fn ($query) => $query->whereNull('irmao_id')->orWhere('irmao_id', $irmao->id))
            ->orderBy('name')
            ->pluck('name', 'id');

        return view('admin.irmaos.edit', compact('irmao', 'usuariosDisponiveis'));
    }

    public function update(AtualizarIrmaoRequest $request, Irmao $irmao): RedirectResponse
    {
        $dados = $request->validated();

        $valoresAnteriores = $irmao->only(array_keys(self::CAMPOS_HISTORIADOS));

        $irmao->fill(collect($dados)->except(['fotografia', 'usuario_id'])->all());
        $irmao->save();

        if ($request->hasFile('fotografia')) {
            $irmao->update(['fotografia' => $this->armazenarFotografia($request, $irmao)]);
        }

        $usuarioAnteriorId = $irmao->usuario?->id;
        $novoUsuarioId = isset($dados['usuario_id']) ? (int) $dados['usuario_id'] : null;

        if ($usuarioAnteriorId !== $novoUsuarioId) {
            if ($usuarioAnteriorId) {
                User::whereKey($usuarioAnteriorId)->update(['irmao_id' => null]);
            }

            if ($novoUsuarioId) {
                User::whereKey($novoUsuarioId)->update(['irmao_id' => $irmao->id]);
            }
        }

        foreach (self::CAMPOS_HISTORIADOS as $campo => $tipo) {
            $valorAnterior = $valoresAnteriores[$campo] instanceof \BackedEnum
                ? $valoresAnteriores[$campo]->value
                : $valoresAnteriores[$campo];

            $valorNovo = $dados[$campo] ?? null;

            if ($valorAnterior !== $valorNovo) {
                $this->registrarHistorico($irmao, $tipo, (string) $valorAnterior, (string) $valorNovo);
            }
        }

        RegistradorDeAuditoria::registrar(
            acao: 'editar',
            modulo: 'irmaos',
            entidade: 'Irmao',
            entidadeId: $irmao->id,
            dadosAnteriores: $valoresAnteriores,
            dadosNovos: $irmao->only(array_keys(self::CAMPOS_HISTORIADOS)),
        );

        return redirect()
            ->route('admin.irmaos.show', $irmao)
            ->with('sucesso', 'Irmão atualizado com sucesso.');
    }

    public function destroy(Irmao $irmao): RedirectResponse
    {
        $this->authorize('delete', $irmao);

        $irmao->delete();

        RegistradorDeAuditoria::registrar('excluir', 'irmaos', 'Irmao', $irmao->id);

        return redirect()
            ->route('admin.irmaos.index')
            ->with('sucesso', 'Irmão removido com sucesso.');
    }

    public function foto(Irmao $irmao): StreamedResponse
    {
        $this->authorize('view', $irmao);

        abort_unless($irmao->fotografia, 404);

        return Storage::disk('local')->response($irmao->fotografia);
    }

    private function armazenarFotografia(CriarIrmaoRequest|AtualizarIrmaoRequest $request, Irmao $irmao): string
    {
        if ($irmao->fotografia) {
            Storage::disk('local')->delete($irmao->fotografia);
        }

        // Fotografias são dados sensíveis do Irmão: armazenadas no disco privado
        // e servidas apenas via Controller + Policy (nunca por URL pública direta).
        return $request->file('fotografia')->store('irmaos/fotos', 'local');
    }

    private function registrarHistorico(Irmao $irmao, TipoHistoricoIrmao $tipo, ?string $anterior, ?string $novo): void
    {
        $irmao->historicos()->create([
            'tipo' => $tipo,
            'valor_anterior' => $anterior,
            'valor_novo' => $novo,
            'data_referencia' => now()->toDateString(),
            'registrado_por' => Auth::id(),
        ]);
    }

    private function registrarHistoricoCadastral(Irmao $irmao, string $observacao): void
    {
        $irmao->historicos()->create([
            'tipo' => TipoHistoricoIrmao::CADASTRAL,
            'data_referencia' => now()->toDateString(),
            'observacao' => $observacao,
            'registrado_por' => Auth::id(),
        ]);
    }
}
