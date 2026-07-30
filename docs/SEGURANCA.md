# Segurança

## Medidas já implementadas

- **Autenticação**: Laravel Breeze (sessão), com rate limiting nativo no login (`RateLimiter`, 5 tentativas).
- **Cadastro público desabilitado**: não existe rota de registro; usuários são criados apenas por administradores autorizados (`usuarios.criar`).
- **Bloqueio/inatividade**: usuários inativos ou bloqueados (`bloqueado_em` preenchido) não conseguem autenticar, mesmo com credenciais corretas (`LoginRequest::authenticate()`).
- **CSRF**: proteção padrão do Laravel em todos os formulários.
- **Mass assignment**: `User` usa o atributo `#[Fillable(...)]` explícito; campos sensíveis (`status`, `bloqueado_em`, `deve_alterar_senha`) só são alterados por métodos específicos do Controller, nunca por preenchimento genérico de formulário.
- **Autorização em duas camadas**: Policies (`App\Policies`) para regras ligadas a um Model e permissões nomeadas (`spatie/laravel-permission`) para autorizações independentes de instância. Nunca há autorização apenas por ocultação de botão na interface — todas as ações administrativas chamam `$this->authorize(...)` no Controller.
- **Superadministrador protegido**: bypass total via `Gate::before`, para nunca perder acesso acidentalmente por remoção de permissão.
- **Auditoria**: ações sensíveis sobre usuários (criar, editar, ativar, desativar, bloquear, desbloquear) são registradas em `auditorias`, sem armazenar senhas, tokens ou segredos.
- **Senhas com hash**: `bcrypt` via cast `hashed` do Eloquent.
- **Testes de isolamento**: banco de testes (SQLite em memória) isolado do banco de desenvolvimento (MySQL).

## Pendências / a implementar em fases futuras

- **Verificação de e-mail temporariamente desativada** (middleware `verified` removido de `routes/web.php` e `routes/admin.php`): sem SMTP configurado, o e-mail de verificação nunca chega ao usuário, o que deixava contas recém-criadas pelo administrador travadas na tela de confirmação. Reativar assim que o envio real de e-mail estiver configurado (Fase 11) — ver `docs/MODULOS.md`.
- Fluxo de **alteração do e-mail de acesso** (exigindo senha atual + confirmação por e-mail) descrito na seção 9.1 do escopo ainda não foi implementado — ver `docs/MODULOS.md`.
- Upload de arquivos (fotos, documentos): validação de MIME real (não apenas extensão), dimensões e armazenamento fora da pasta pública para documentos privados. Fase 9 implementou anexos privados do módulo de Documentos e Trabalhos no disco `local`; Galeria ainda será tratada em fase própria.
- Download de documentos privados sempre por Controller + Policy, nunca por URL pública direta. Implementado para Documentos e Trabalhos por `DocumentoArquivoPolicy::download`.
- Configuração de e-mail (SMTP) pelo painel, com senha criptografada e nunca exibida — módulo ainda não iniciado (Fase 11).
- Cabeçalhos de segurança HTTP (CSP, HSTS etc.) e cookies seguros em produção — a revisar na Fase 13 (Produção).
- 2FA (autenticação de dois fatores) — fase futura, conforme escopo original.

## LGPD

Ver `docs/LGPD.md`.
