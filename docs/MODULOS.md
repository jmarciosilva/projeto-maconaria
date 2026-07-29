# Módulos — Decisões e Suposições Registradas

Este documento registra decisões e suposições tomadas quando o escopo original não detalhava uma regra de negócio específica, conforme a regra de execução: *"Quando uma regra de negócio não estiver definida, registre uma suposição... utilize uma implementação conservadora e documente a pendência."*

## Autenticação e usuários

- **Cadastro público removido por completo**: em vez de apenas esconder o link de registro, as rotas, o Controller (`RegisteredUserController`), a view (`auth/register.blade.php`) e o teste (`RegistrationTest`) do Breeze foram excluídos. Justificativa: a seção 9.1 do escopo proíbe explicitamente o autocadastro; manter código morto/rota inacessível só por trás de uma condição de UI violaria a regra "não implementar acesso apenas escondendo opções no menu" (seção 32.18).
- **Rota `/dashboard` renomeada para `/area-restrita`** (nome de rota `area-restrita`): a seção 5 do escopo lista `/area-restrita` como URL canônica da área restrita; o Breeze usa `/dashboard` por padrão. Todos os redirecionamentos pós-login/verificação de e-mail/confirmação de senha foram atualizados.
- **Fluxo completo de alteração de e-mail (seção 9.1)** — exigir senha atual, validar novo e-mail, confirmação por e-mail e registro em auditoria — **ainda não implementado**. O Breeze só oferece atualização direta de nome/e-mail (invalidando `email_verified_at`). Pendente para uma iteração futura do módulo de Usuários.
- **Timezone padrão alterado de `UTC` para `America/Sao_Paulo`** em `config/app.php`, por ser uma instituição sediada no Brasil. Configurável futuramente pelo módulo de Configurações (Fase 13/seção 9.17).

## Perfis e permissões

- **Mapeamento perfil → permissões**: a seção 9.4 do escopo lista os perfis e um catálogo de exemplos de permissões, mas não define exatamente quais permissões cada perfil recebe. O mapeamento em `database/seeders/PerfilPermissaoSeeder.php` é uma suposição conservadora (ex.: Tesoureiro recebe `tesouraria.*`, Secretário recebe `secretaria.*` + `eventos.*` etc.) e deve ser revisado por um administrador da Loja quando os módulos correspondentes forem construídos.
- **Permissões `perfis.visualizar`, `perfis.criar`, `perfis.editar`, `perfis.excluir`** foram adicionadas além do catálogo original da seção 9.4, para separar "gerenciar as definições de perfis" (roles) de "atribuir perfis existentes a um usuário" (`usuarios.atribuir-perfis`, que já constava no escopo).
- **CRUD completo de Perfis pelo painel** ainda não existe — atualmente `admin/perfis` é somente leitura (lista perfis e suas permissões). Criação/edição de perfis pelo painel fica para uma fase futura, quando as regras de "perfis configuráveis" (seção 9.4) forem detalhadas.

## Cadastro de Irmãos (Fase 2 — ainda não iniciada)

- O campo `irmao_id` em `users` (vínculo opcional entre usuário e Irmão, seção 9.2) **não foi criado** nesta entrega, pois a tabela `irmaos` ainda não existe. Será adicionado como migration incremental (coluna nullable + FK) quando a Fase 2 for iniciada.

## CMS e landing page (Fase 3 — parcialmente iniciada)

- A página inicial pública (`site/home.blade.php`) é um placeholder estático, não administrável pelo CMS ainda. O menu público criado (`x-layouts.site`) contém apenas "Início" e "Entrar"/"Área Restrita", porque as demais páginas institucionais (`/noticias`, `/sobre-nos`, `/maconaria` etc., seção 5) ainda não existem — evitando links mortos no menu.

## Componentes de UI

- Componentes criados nesta entrega: `x-ui.alert`, `x-ui.button`, `x-ui.badge`, `x-ui.empty-state`, `x-ui.table`, `x-ui.confirmation`, `x-ui.input`, `x-ui.select`. O componente `x-ui.modal` já vem do Breeze (`components/modal.blade.php`) e foi reaproveitado.
- **Paginação**: não foi criado um `x-ui.pagination` próprio — a view de paginação padrão do Laravel já é estilizada com Tailwind e foi usada diretamente (`{{ $usuarios->links() }}`). Um wrapper dedicado pode ser criado depois se for necessário customizar o visual.

## Ferramentas de qualidade

- **Larastan/PHPStan**: instalado e configurado (`phpstan.neon.dist`, script `composer analyse`), porém **não foi possível executar `composer analyse` com sucesso dentro do ambiente sandbox desta sessão** — o processo `analyse` encerra silenciosamente (sem saída, código de saída 1) mesmo em um único arquivo trivial, enquanto `phpstan --version` funciona normalmente. Isso indica uma limitação do ambiente de execução da sessão (não um problema de configuração do projeto). **Recomendação**: executar `composer analyse` localmente no Laragon para validar antes de confiar no resultado.
