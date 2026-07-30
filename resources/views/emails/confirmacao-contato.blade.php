<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #eef3fb; padding: 32px 16px; font-family: Arial, Helvetica, sans-serif;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width: 560px; background-color: #ffffff; border-radius: 12px; overflow: hidden; border: 1px solid #dbe4f5;">
                <tr>
                    <td style="background-color: #1d2a5c; padding: 24px 32px; color: #ffffff; font-size: 17px; font-weight: bold;">
                        {{ $configuracao->nome() }}
                    </td>
                </tr>

                <tr>
                    <td style="padding: 36px 32px 8px;">
                        <p style="margin: 0 0 4px; font-size: 13px; font-weight: bold; letter-spacing: 0.06em; text-transform: uppercase; color: #4a6da8;">Mensagem recebida</p>
                        <h1 style="margin: 0; font-size: 22px; color: #1d2a5c;">Olá, {{ $nomeRemetente }}!</h1>
                    </td>
                </tr>

                <tr>
                    <td style="padding: 12px 32px 0; font-size: 15px; line-height: 1.6; color: #33415c;">
                        <p style="margin: 0 0 16px;">Recebemos sua mensagem sobre <strong>&ldquo;{{ $assunto }}&rdquo;</strong> e agradecemos o contato.</p>
                        <p style="margin: 0 0 16px;">Em breve um de nossos irmãos entrará em contato com você. Este é apenas um retorno automático confirmando o recebimento — não é necessário responder este e-mail.</p>
                    </td>
                </tr>

                <tr>
                    <td style="padding: 8px 32px 36px;">
                        <table role="presentation" cellpadding="0" cellspacing="0" style="width: 100%; background-color: #eef3fb; border-radius: 8px;">
                            <tr>
                                <td style="padding: 16px 20px; font-size: 14px; color: #33415c;">
                                    Fraternalmente,<br>
                                    <strong>{{ $configuracao->nome() }}</strong>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="padding: 20px 32px; background-color: #f7f9fd; border-top: 1px solid #dbe4f5; font-size: 12px; color: #8493ad; text-align: center;">
                        @if ($configuracao->endereco_rodape)
                            <p style="margin: 0 0 4px; white-space: pre-line;">{{ $configuracao->endereco_rodape }}</p>
                        @endif
                        @if ($configuracao->telefone_institucional)
                            <p style="margin: 0;">{{ $configuracao->telefone_institucional }}</p>
                        @endif
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
