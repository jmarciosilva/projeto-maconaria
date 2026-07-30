<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\ConfiguracaoInstitucional;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

final class ConfirmacaoContatoRecebido extends Mailable
{
    public function __construct(
        public readonly string $nomeRemetente,
        public readonly string $assunto,
        public readonly ConfiguracaoInstitucional $configuracao,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Recebemos sua mensagem — '.$this->configuracao->nome(),
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.confirmacao-contato');
    }
}
