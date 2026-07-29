# Changelog

Todas as alterações relevantes deste projeto são documentadas neste arquivo.

## [Não lançado]

### Adicionado

- Fundação do projeto: instalação do Laravel 13 com PHP 8.3, MySQL, Tailwind CSS v4 e Vite.
- Autenticação via Laravel Breeze (stack Blade): login, logout, recuperação de senha, verificação de e-mail, confirmação de senha. Cadastro público desabilitado — usuários são criados apenas por administradores.
- Localização pt_BR (validação, autenticação, paginação, senhas) via `laravel-lang`.
- Papéis e permissões via `spatie/laravel-permission`, com seed inicial de perfis e permissões (`PerfilPermissaoSeeder`).
- Módulo inicial de Usuários no painel administrativo (CRUD, ativar/desativar, bloquear/desbloquear, atribuição de perfis).
- Trilha de auditoria inicial (`Auditoria` + `RegistradorDeAuditoria`).
- Layouts base: `x-layouts.site`, `x-layouts.admin`, `x-layouts.restrito`, e componentes de UI reutilizáveis (`x-ui.*`).
- Seeder do administrador local (`AdministradorLocalSeeder`), configurável via `.env`.
- Testes automatizados (autenticação, permissões, auditoria) e Laravel Pint configurado.
- Documentação inicial (`README.md`, `ROADMAP.md`, `CONTRIBUTING.md`, `docs/*`).
