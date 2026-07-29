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

## Cadastro de Irmãos (Fase 2)

- **Direção do vínculo Usuário ↔ Irmão**: a seção 9.2 descreve o campo do lado do Usuário ("Irmão Maçom vinculado") e a seção 9.3 descreve o campo do lado do Irmão ("Usuário vinculado"). Como é uma relação 1:1 opcional, a chave estrangeira física foi colocada em `users.irmao_id` (não em `irmaos.usuario_id`), com `unique` para garantir que um usuário nunca esteja vinculado a mais de um Irmão. A tela de vínculo (selecionar o usuário) fica no formulário do Irmão, por ser o cadastro mais completo.
- **Situação cadastral**: os valores exatos não foram especificados no escopo. Adotado o enum `SituacaoCadastralIrmao`: `ativo`, `inativo`, `licenciado`, `irregular`, `desligado`, `falecido` — um superconjunto conservador cobrindo os casos mais comuns de administração de Loja.
- **Grau atual**: enum `GrauMaconico` com os três graus simbólicos (`aprendiz`, `companheiro`, `mestre`).
- **Cargo atual**: implementado como campo de **texto livre** (`cargo_atual`), não como enum/tabela de lookup. O escopo não define a lista fechada de cargos da Loja, e criar uma tabela de administração de cargos seria uma nova sub-funcionalidade fora do previsto para a Fase 2. Pode ser convertido para um catálogo configurável no futuro, se necessário.
- **Histórico consolidado**: em vez de quatro tabelas separadas para cargo, grau, situação cadastral e "demais alterações cadastrais" (conforme o texto literal da seção 9.3), foi criada uma única tabela `irmao_historicos` discriminada pelo campo `tipo` (ver ADR e `docs/ARQUITETURA.md`). Isso evita quatro tabelas quase idênticas para o volume de dados desta fase.
- **CPF**: validado por dígitos verificadores reais (`App\Rules\CpfValido`), não apenas máscara/formato. **Não é criptografado em repouso** — a proteção se dá por controle de acesso (permissão `irmaos.visualizar`) e por `#[Hidden(['cpf', ...])]` no Model (nunca aparece em serializações JSON/array). Criptografia em coluna traria complicações para a constraint `unique` e buscas, e não foi solicitada explicitamente no escopo.
- **Fotografia**: armazenada no disco privado `local` (`storage/app/private`, sem link público) e servida exclusivamente via `IrmaoController::foto()` com `Policy` (`view`) — nunca por URL pública direta, seguindo o mesmo princípio definido para documentos privados (seção 27), aplicado proativamente aqui por se tratar de dado sensível de um Irmão.
- **Preenchimento automático de endereço por CEP**: o campo CEP foi movido para o início do bloco "Endereço" e, ao completar 8 dígitos, o frontend consulta o [ViaCEP](https://viacep.com.br) (API pública, gratuita, sem necessidade de chave de acesso) diretamente do navegador (`resources/js/app.js`) e preenche Endereço, Bairro, Cidade e Estado. Não há proxy no backend Laravel para essa consulta — é uma chamada `fetch` client-side simples, dado que o ViaCEP já oferece CORS público e não expõe nenhum dado sensível da aplicação. Se o serviço estiver indisponível ou o CEP não for encontrado, o usuário recebe uma mensagem e pode preencher manualmente (nenhum campo é obrigatório nem bloqueado por essa integração). Telefone e CEP têm máscara e validação de formato (`(00) 00000-0000` e `00000-000`, respectivamente) tanto no cadastro de Usuários quanto no de Irmãos.
- **Exclusão**: `Irmao` usa soft delete (`SoftDeletes`) — remover um Irmão pelo painel não apaga o registro fisicamente.

## CMS e landing page (Fase 3 — parcialmente iniciada)

- A página inicial pública (`site/home.blade.php`) é um placeholder estático, não administrável pelo CMS ainda. O menu público criado (`x-layouts.site`) contém apenas "Início" e "Entrar"/"Área Restrita", porque as demais páginas institucionais (`/noticias`, `/sobre-nos`, `/maconaria` etc., seção 5) ainda não existem — evitando links mortos no menu.

## Componentes de UI

- Componentes criados nesta entrega: `x-ui.alert`, `x-ui.button`, `x-ui.badge`, `x-ui.empty-state`, `x-ui.table`, `x-ui.confirmation`, `x-ui.input`, `x-ui.select`. O componente `x-ui.modal` já vem do Breeze (`components/modal.blade.php`) e foi reaproveitado.
- **Paginação**: não foi criado um `x-ui.pagination` próprio — a view de paginação padrão do Laravel já é estilizada com Tailwind e foi usada diretamente (`{{ $usuarios->links() }}`). Um wrapper dedicado pode ser criado depois se for necessário customizar o visual.

## Ferramentas de qualidade

- **Larastan/PHPStan**: instalado e configurado (`phpstan.neon.dist`, script `composer analyse`), porém **não foi possível executar `composer analyse` com sucesso dentro do ambiente sandbox desta sessão** — o processo `analyse` encerra silenciosamente (sem saída, código de saída 1) mesmo em um único arquivo trivial, enquanto `phpstan --version` funciona normalmente. Isso indica uma limitação do ambiente de execução da sessão (não um problema de configuração do projeto). **Recomendação**: executar `composer analyse` localmente no Laragon para validar antes de confiar no resultado.
