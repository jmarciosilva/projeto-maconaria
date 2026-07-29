# Banco de Dados

## Convenções

- Tabelas e colunas em `snake_case`, em português (ex.: `usuarios`, `criado_em`) ou inglês quando gerado por pacotes de terceiros (ex.: as tabelas do `spatie/laravel-permission`: `roles`, `permissions`, `model_has_roles` etc., mantidas com os nomes originais do pacote para evitar customização frágil).
- Charset/collation: `utf8mb4` / `utf8mb4_unicode_ci`.
- Valores monetários futuros (Tesouraria) usarão `DECIMAL`, nunca `FLOAT`.
- Operações que alteram múltiplas tabelas usam `DB::transaction()`.
- Exclusão lógica (soft deletes) será adotada nos módulos administrativos que precisarem de histórico (ex.: Irmãos, Tesouraria, Secretaria), quando esses módulos forem implementados.

## Tabelas atuais

### `users` (Laravel padrão + campos administrativos)

| Coluna | Tipo | Descrição |
|---|---|---|
| `name` | string | Nome do usuário |
| `email` | string, único | E-mail de acesso |
| `telefone` | string, nullable | Telefone de contato |
| `password` | string (hash) | Senha |
| `status` | string (`StatusUsuario`) | `ativo` \| `inativo` |
| `deve_alterar_senha` | boolean | Exige troca de senha no próximo login |
| `bloqueado_em` | timestamp, nullable | Data/hora do bloqueio administrativo (`null` = não bloqueado) |
| `ultimo_acesso_em` | timestamp, nullable | Atualizado a cada login bem-sucedido |
| `email_verified_at` | timestamp, nullable | Verificação de e-mail |

| `irmao_id` | FK nullable, único → `irmaos` | Irmão vinculado a este usuário (adicionado na Fase 2) |

### `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`

Geradas pelo `spatie/laravel-permission` (migration `create_permission_tables`). Guardam os perfis, permissões e seus vínculos com os usuários.

### `irmaos` (Fase 2)

| Coluna | Tipo | Descrição |
|---|---|---|
| `nome_completo` | string | Nome completo do Irmão |
| `nome_social` | string, nullable | Nome social |
| `data_nascimento` | date, nullable | |
| `cpf` | string(11), único | Validado por `App\Rules\CpfValido` (dígitos verificadores) |
| `rg` | string, nullable | |
| `email`, `telefone` | string, nullable | Contato do Irmão (distinto do e-mail de acesso do usuário) |
| `endereco`, `numero`, `complemento`, `bairro`, `cidade`, `estado`, `cep` | string, nullable | Endereço |
| `data_iniciacao`, `data_elevacao`, `data_exaltacao` | date, nullable | |
| `cim` | string, nullable | CIM ou matrícula |
| `grau_atual` | string (`GrauMaconico`), nullable | aprendiz \| companheiro \| mestre |
| `situacao_cadastral` | string (`SituacaoCadastralIrmao`) | ativo \| inativo \| licenciado \| irregular \| desligado \| falecido |
| `cargo_atual` | string, nullable | Texto livre (ver `docs/MODULOS.md`) |
| `data_ingresso_loja`, `data_desligamento` | date, nullable | |
| `observacoes_administrativas` | text, nullable | Sensível — nunca exposta publicamente |
| `fotografia` | string, nullable | Caminho no disco privado `local` (não no disco `public`) |
| Soft deletes | `deleted_at` | Exclusão lógica |

O vínculo com `users` é opcional e fica em `users.irmao_id` (não em `irmaos.usuario_id`) — um Irmão pode não ter usuário de acesso, e um usuário só pode estar vinculado a, no máximo, um Irmão (`unique` em `users.irmao_id`).

### `irmao_historicos` (Fase 2)

Tabela única para o histórico de cargo, grau, situação cadastral e demais alterações cadastrais relevantes, discriminada pelo campo `tipo` (`App\Enums\TipoHistoricoIrmao`). Optou-se por uma tabela consolidada em vez de quatro tabelas separadas — ver `docs/MODULOS.md`.

| Coluna | Tipo | Descrição |
|---|---|---|
| `irmao_id` | FK → `irmaos` | |
| `tipo` | string | `cargo` \| `grau` \| `situacao_cadastral` \| `cadastral` |
| `valor_anterior`, `valor_novo` | string, nullable | |
| `data_referencia` | date | Data efetiva da alteração |
| `observacao` | text, nullable | |
| `registrado_por` | FK nullable → `users` | Quem registrou |
| `criado_em` | timestamp | Imutável |

### `auditorias`

| Coluna | Tipo | Descrição |
|---|---|---|
| `usuario_id` | FK nullable → `users` | Quem executou a ação (nulo se sistema) |
| `acao` | string | Ex.: `criar`, `editar`, `ativar`, `bloquear` |
| `modulo` | string | Ex.: `usuarios` |
| `entidade` | string, nullable | Nome da entidade afetada |
| `entidade_id` | unsigned bigint, nullable | ID da entidade afetada |
| `dados_anteriores` | json, nullable | Estado anterior relevante (nunca senhas/segredos) |
| `dados_novos` | json, nullable | Estado novo relevante |
| `endereco_ip` | string | IP da requisição |
| `user_agent` | string | User agent da requisição |
| `criado_em` | timestamp | Data/hora do registro (imutável, sem `updated_at`) |

### `configuracoes_institucionais` (Fase 3 — singleton)

Tabela com um único registro (`id = 1`), acessado via `ConfiguracaoInstitucional::atual()`. Evita a complexidade de uma tabela chave-valor genérica para um punhado de campos que sempre existem juntos.

| Coluna | Tipo | Descrição |
|---|---|---|
| `logotipo` | string, nullable | Caminho no disco público `public` |
| `endereco_rodape` | text, nullable | Exibido no rodapé do site público |
| `email_institucional` | string, nullable | E-mail público exibido no rodapé |
| `facebook_url`, `instagram_url`, `twitter_url`, `tiktok_url` | string, nullable | Links das redes sociais |

### `carrossel_itens` (Fase 3)

| Coluna | Tipo | Descrição |
|---|---|---|
| `titulo`, `subtitulo` | string, nullable | |
| `imagem_desktop` | string | Obrigatória, disco público |
| `imagem_mobile` | string, nullable | Se ausente, a home usa `imagem_desktop` como fallback |
| `texto_alternativo` | string | Obrigatório (acessibilidade) |
| `link`, `texto_botao` | string, nullable | Chamada para ação opcional |
| `abrir_em_nova_aba` | boolean | |
| `ordem` | unsigned int | Ordenação manual (ver `docs/MODULOS.md`) |
| `data_inicio`, `data_fim` | date, nullable | Período de exibição; `null` = sem limite naquele lado |
| `ativo` | boolean | |

O nome da tabela (`carrossel_itens`, plural em português) diverge da convenção automática do Eloquent (que geraria `carrossel_items`), por isso o Model `CarrosselItem` declara `protected $table` explicitamente. O mesmo vale para `ConfiguracaoInstitucional` → `configuracoes_institucionais`.

## Seeders

- `PerfilPermissaoSeeder`: cria o catálogo de permissões e os perfis iniciais.
- `AdministradorLocalSeeder`: cria/atualiza o administrador local a partir das variáveis `ADMIN_*` do `.env`. Nunca executa em produção (`App::environment('production')` é verificado explicitamente).

## Migrations relevantes

- `2026_07_29_130150_create_permission_tables` (spatie/laravel-permission)
- `2026_07_29_130231_adiciona_campos_administrativos_a_usuarios`
- `2026_07_29_130232_cria_tabela_auditorias`
- `2026_07_29_112902_cria_tabela_irmaos`
- `2026_07_29_112904_cria_tabela_irmao_historicos`
- `2026_07_29_112905_adiciona_irmao_id_a_usuarios`
- `2026_07_29_142844_cria_tabela_configuracoes_institucionais`
- `2026_07_29_142845_cria_tabela_carrossel_itens`
