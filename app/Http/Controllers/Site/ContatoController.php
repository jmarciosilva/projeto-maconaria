<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\Site\EnviarContatoRequest;
use App\Mail\ConfirmacaoContatoRecebido;
use App\Mail\MensagemDeContato;
use App\Models\ConfiguracaoInstitucional;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

final class ContatoController extends Controller
{
    public function mostrar(): View
    {
        return view('site.contato');
    }

    public function enviar(EnviarContatoRequest $request): RedirectResponse
    {
        // Campo-armadilha preenchido: é robô de spam. Devolve a mesma
        // resposta de sucesso, sem enviar nada, para não revelar ao robô
        // que a submissão foi identificada e descartada.
        if (filled($request->validated('site'))) {
            return back()->with('sucesso', 'Mensagem enviada com sucesso! Em breve entraremos em contato.');
        }

        $configuracao = ConfiguracaoInstitucional::atual();
        $nome = $request->validated('nome');
        $email = $request->validated('email');
        $assunto = $request->validated('assunto');

        try {
            Mail::to($configuracao->email_institucional)->send(new MensagemDeContato(
                nome: $nome,
                emailRemetente: $email,
                telefone: $request->validated('telefone'),
                assunto: $assunto,
                mensagem: $request->validated('mensagem'),
            ));
        } catch (TransportExceptionInterface $exception) {
            Log::error('Falha ao enviar mensagem de contato do site.', [
                'erro' => $exception->getMessage(),
            ]);

            return back()->withInput()->with('erro', 'Não foi possível enviar sua mensagem agora. Tente novamente em instantes.');
        }

        // Falha ao enviar a confirmação para o remetente não deve virar erro
        // para o usuário: a mensagem principal já chegou à Loja, que é o que
        // importa. Só registra em log para acompanhamento.
        try {
            Mail::to($email)->send(new ConfirmacaoContatoRecebido(
                nomeRemetente: $nome,
                assunto: $assunto,
                configuracao: $configuracao,
            ));
        } catch (TransportExceptionInterface $exception) {
            Log::error('Falha ao enviar confirmação de contato ao remetente.', [
                'erro' => $exception->getMessage(),
            ]);
        }

        return back()->with('sucesso', 'Mensagem enviada com sucesso! Em breve entraremos em contato.');
    }
}
