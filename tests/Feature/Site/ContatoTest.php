<?php

namespace Tests\Feature\Site;

use App\Mail\ConfirmacaoContatoRecebido;
use App\Mail\MensagemDeContato;
use App\Models\ConfiguracaoInstitucional;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContatoTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_page_is_publicly_accessible(): void
    {
        $this->get(route('contato.mostrar'))
            ->assertOk()
            ->assertSee('Fale com a Loja');
    }

    public function test_valid_submission_sends_message_to_institutional_email(): void
    {
        Mail::fake();

        ConfiguracaoInstitucional::query()->create(['email_institucional' => 'contato@arlsferrazdevasconcelos.com.br']);

        $this->post(route('contato.enviar'), [
            'nome' => 'João da Silva',
            'email' => 'joao@example.com',
            'telefone' => '(11) 99999-9999',
            'assunto' => 'Dúvida sobre visitação',
            'mensagem' => 'Gostaria de saber como posso visitar a Loja.',
        ])
            ->assertRedirect()
            ->assertSessionHas('sucesso');

        Mail::assertSent(MensagemDeContato::class, function (MensagemDeContato $mail) {
            return $mail->nome === 'João da Silva'
                && $mail->emailRemetente === 'joao@example.com'
                && $mail->hasTo('contato@arlsferrazdevasconcelos.com.br');
        });

        Mail::assertSent(ConfirmacaoContatoRecebido::class, function (ConfirmacaoContatoRecebido $mail) {
            return $mail->nomeRemetente === 'João da Silva'
                && $mail->assunto === 'Dúvida sobre visitação'
                && $mail->hasTo('joao@example.com');
        });
    }

    public function test_submission_with_filled_honeypot_is_silently_discarded(): void
    {
        Mail::fake();

        $this->post(route('contato.enviar'), [
            'nome' => 'Robô Spam',
            'email' => 'spam@example.com',
            'assunto' => 'Oferta imperdível',
            'mensagem' => 'Compre agora.',
            'site' => 'http://spam.example.com',
        ])
            ->assertRedirect()
            ->assertSessionHas('sucesso');

        Mail::assertNothingSent();
    }

    public function test_submission_requires_mandatory_fields(): void
    {
        $this->post(route('contato.enviar'), [])
            ->assertSessionHasErrors(['nome', 'email', 'assunto', 'mensagem']);
    }

    public function test_submission_with_invalid_email_fails_validation(): void
    {
        $this->post(route('contato.enviar'), [
            'nome' => 'João da Silva',
            'email' => 'nao-e-um-email',
            'assunto' => 'Teste',
            'mensagem' => 'Mensagem de teste.',
        ])
            ->assertSessionHasErrors(['email']);
    }
}
