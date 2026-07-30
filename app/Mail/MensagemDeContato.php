<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

final class MensagemDeContato extends Mailable
{
    public function __construct(
        public readonly string $nome,
        public readonly string $emailRemetente,
        public readonly ?string $telefone,
        public readonly string $assunto,
        public readonly string $mensagem,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Contato pelo site: '.$this->assunto,
            // Responder este e-mail vai direto para quem preencheu o
            // formulário, sem precisar copiar o endereço manualmente.
            replyTo: [new Address($this->emailRemetente, $this->nome)],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.mensagem-contato');
    }
}
