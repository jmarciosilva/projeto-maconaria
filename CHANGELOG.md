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
- **Fase 2 — Cadastro de Irmãos**: módulo completo (`Irmao`, `IrmaoHistorico`), com CPF validado por dígitos verificadores (`App\Rules\CpfValido`), histórico unificado de cargo/grau/situação cadastral/alterações cadastrais, vínculo opcional 1:1 com usuário (`users.irmao_id`), fotografia armazenada em disco privado e servida apenas via Controller autenticado + Policy, e proteção de dados sensíveis (CPF/RG/observações ocultos em serializações). CRUD completo no painel administrativo (`admin/irmaos`), com permissões `irmaos.*` concedidas a Administrador e Secretário.
- **Fase 3 — CMS institucional e carrossel (parcial)**: configurações institucionais administráveis pelo painel (`admin/configuracoes/institucional`) — logotipo, endereço para rodapé, e-mail institucional e redes sociais (Facebook, Instagram, Twitter/X, TikTok), armazenadas em registro único (`ConfiguracaoInstitucional::atual()`). Carrossel da página inicial com CRUD completo (`admin/carrossel`): imagem desktop/mobile, período de exibição, ordem, texto alternativo. Página inicial pública agora renderiza o carrossel dinâmico (Alpine.js, com fallback estático quando vazio) e o cabeçalho/rodapé do site usam o logotipo, endereço, e-mail e redes sociais configurados. Menu, SEO, nome/título institucional editável e páginas de conteúdo (Sobre Nós, O que é Maçonaria etc.) permanecem pendentes — ver `docs/MODULOS.md`.
- **Fase 3 — Páginas institucionais e identidade completa (continuação)**: adicionados nome da Loja, título/subtítulo institucional e telefone público às configurações institucionais. Novo módulo de Páginas Institucionais (`admin/paginas-institucionais`) com editor de texto rico Quill.js e sanitização de HTML no backend (`mews/purifier`) — cobre Sobre Nós, O que é Maçonaria, Maçonaria para Jovens, Como a Maçonaria pode mudar um cidadão, Política de Privacidade e Termos de Uso, seedadas com conteúdo placeholder e URLs fixas amigáveis. Rodapé do site agora linka política de privacidade/termos de uso; menu do site ganhou um dropdown "Institucional" com as páginas de conteúdo. SEO básico (meta título/descrição) por página.
- **Fase 4 — Notícias e conteúdo**: adicionados categorias, tags, notícias, fluxo editorial (`rascunho`, `agendada`, `publicada`, `arquivada`), agendamento, destaques na home, visibilidade pública/restrita e histórico de versões. O painel administrativo ganhou CRUD de notícias e gestão inicial de categorias/tags; o site público ganhou listagem e leitura de notícias, sempre filtrando conteúdo restrito.
- **Fase 5 — Eventos e calendário**: adicionados eventos e sessões no módulo de agenda, calendário administrativo, listagem pública de eventos, agenda restrita, confirmação/cancelamento de presença, capacidade opcional e separação entre exibição pública e restrita.
- **Fase 6 — Secretaria**: adicionados documentos da Secretaria com tipos para atas, correspondências e documentos oficiais, numeração automática por tipo/ano, versões, aprovação, publicação e auditoria.
- **Fase 7 — Chancelaria**: adicionados registro de frequência por evento/sessão, controle de presenças, ausências e justificativas, cadastro de visitantes, comunicados internos e painel de relatórios.
