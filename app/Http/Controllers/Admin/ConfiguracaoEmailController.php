<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Exceptions\FalhaEnvioEmailException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EnviarEmailTesteRequest;
use App\Http\Requests\Admin\SalvarConfiguracaoEmailRequest;
use App\Mail\EmailTesteConfiguracao;
use App\Models\ConfiguracaoEmail;
use App\Support\Email\AplicadorConfiguracaoEmail;
use App\Support\RegistradorDeAuditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

final class ConfiguracaoEmailController extends Controller
{
    /**
     * Campos não sensíveis usados no antes/depois da auditoria. A senha
     * nunca é incluída aqui, mesmo criptografada.
     *
     * @var list<string>
     */
    private const CAMPOS_AUDITAVEIS = [
        'mailer', 'host', 'porta', 'usuario', 'criptografia', 'remetente_nome', 'remetente_email', 'ativo',
    ];

    public function edit(): View
    {
        $this->authorize('configuracoes.visualizar');

        $configuracao = ConfiguracaoEmail::atual();

        return view('admin.configuracoes.email', compact('configuracao'));
    }

    public function update(SalvarConfiguracaoEmailRequest $request): RedirectResponse
    {
        $configuracao = ConfiguracaoEmail::atual();
        $anterior = $configuracao->only(self::CAMPOS_AUDITAVEIS);

        DB::transaction(function () use ($request, $configuracao, $anterior): void {
            $dados = $request->safe()->except('senha');
            $configuracao->fill([...$dados, 'ativo' => $request->boolean('ativo')]);

            // Campo em branco mantém a senha já armazenada — do contrário,
            // toda edição da tela (mesmo sem trocar a senha) a apagaria.
            if ($request->filled('senha')) {
                $configuracao->senha = $request->string('senha')->value();
            }

            $configuracao->save();

            RegistradorDeAuditoria::registrar(
                acao: 'editar',
                modulo: 'configuracoes',
                entidade: 'ConfiguracaoEmail',
                entidadeId: $configuracao->id,
                dadosAnteriores: $anterior,
                dadosNovos: $configuracao->only(self::CAMPOS_AUDITAVEIS),
            );
        });

        return redirect()
            ->route('admin.configuracoes.email.edit')
            ->with('sucesso', 'Configurações de e-mail atualizadas com sucesso.');
    }

    public function enviarTeste(EnviarEmailTesteRequest $request): RedirectResponse
    {
        $configuracao = ConfiguracaoEmail::atual();

        try {
            $this->enviarEmailDeTeste($configuracao, $request->string('destinatario')->value());
        } catch (FalhaEnvioEmailException $exception) {
            return back()->with('erro', $exception->getMessage());
        }

        return back()->with('sucesso', 'E-mail de teste enviado com sucesso.');
    }

    private function enviarEmailDeTeste(ConfiguracaoEmail $configuracao, string $destinatario): void
    {
        // Reaplica a configuração antes de testar: garante que o teste use
        // exatamente os dados recém-salvos, sem depender de o mailer já ter
        // sido carregado no boot desta mesma requisição.
        AplicadorConfiguracaoEmail::aplicar();

        try {
            Mail::mailer($configuracao->mailer->value)
                ->to($destinatario)
                ->send(new EmailTesteConfiguracao($configuracao));
        } catch (Throwable $exception) {
            // A mensagem da exceção de transporte SMTP pode conter a própria
            // credencial usada na conexão (DSN com usuário/senha) — por isso
            // nunca é repassada ao log, à auditoria ou ao usuário. Só os
            // dados de conexão não sensíveis (mailer/host/porta) são
            // registrados para diagnóstico.
            Log::warning('Falha ao enviar e-mail de teste SMTP.', [
                'mailer' => $configuracao->mailer->value,
                'host' => $configuracao->host,
                'porta' => $configuracao->porta,
            ]);

            RegistradorDeAuditoria::registrar(
                acao: 'testar-envio-email-falhou',
                modulo: 'configuracoes',
                entidade: 'ConfiguracaoEmail',
                entidadeId: $configuracao->id,
            );

            throw new FalhaEnvioEmailException(
                'Não foi possível enviar o e-mail de teste. Verifique host, porta, usuário, senha e criptografia e tente novamente.'
            );
        }

        RegistradorDeAuditoria::registrar(
            acao: 'testar-envio-email',
            modulo: 'configuracoes',
            entidade: 'ConfiguracaoEmail',
            entidadeId: $configuracao->id,
        );
    }
}
