<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\ConfiguracaoEmail;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

final class EmailTesteConfiguracao extends Mailable
{
    public function __construct(public readonly ConfiguracaoEmail $configuracao) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'E-mail de teste — configuração SMTP',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.teste-configuracao');
    }
}
