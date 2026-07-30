<div style="font-family: sans-serif; font-size: 14px; color: #1f2933;">
    <h1 style="font-size: 18px;">Nova mensagem recebida pelo site</h1>

    <p><strong>Nome:</strong> {{ $nome }}</p>
    <p><strong>E-mail:</strong> {{ $emailRemetente }}</p>
    @if ($telefone)
        <p><strong>Telefone:</strong> {{ $telefone }}</p>
    @endif
    <p><strong>Assunto:</strong> {{ $assunto }}</p>

    <p><strong>Mensagem:</strong></p>
    <p style="white-space: pre-line;">{{ $mensagem }}</p>

    <p style="margin-top: 24px; color: #6b7280;">Para responder, basta usar o "Responder" do seu cliente de e-mail — a resposta vai direto para {{ $emailRemetente }}.</p>
</div>
