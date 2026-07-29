<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AtualizarConfiguracaoInstitucionalRequest;
use App\Models\ConfiguracaoInstitucional;
use App\Support\RegistradorDeAuditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

final class ConfiguracaoInstitucionalController extends Controller
{
    public function edit(): View
    {
        $this->authorize('cms.visualizar');

        $configuracao = ConfiguracaoInstitucional::atual();

        return view('admin.configuracoes.institucional', compact('configuracao'));
    }

    public function update(AtualizarConfiguracaoInstitucionalRequest $request): RedirectResponse
    {
        $configuracao = ConfiguracaoInstitucional::atual();
        $dados = $request->safe()->except('logotipo');

        $anterior = $configuracao->only([
            'endereco_rodape', 'email_institucional', 'facebook_url', 'instagram_url', 'twitter_url', 'tiktok_url',
        ]);

        $configuracao->fill($dados);

        if ($request->hasFile('logotipo')) {
            if ($configuracao->logotipo) {
                Storage::disk('public')->delete($configuracao->logotipo);
            }

            $configuracao->logotipo = $request->file('logotipo')->store('institucional', 'public');
        }

        $configuracao->save();

        RegistradorDeAuditoria::registrar(
            acao: 'editar',
            modulo: 'cms',
            entidade: 'ConfiguracaoInstitucional',
            entidadeId: $configuracao->id,
            dadosAnteriores: $anterior,
            dadosNovos: $configuracao->only([
                'endereco_rodape', 'email_institucional', 'facebook_url', 'instagram_url', 'twitter_url', 'tiktok_url',
            ]),
        );

        return redirect()
            ->route('admin.configuracoes.institucional.edit')
            ->with('sucesso', 'Configurações institucionais atualizadas com sucesso.');
    }
}
