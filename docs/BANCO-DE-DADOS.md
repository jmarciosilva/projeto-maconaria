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

O campo `irmao_id` (vínculo com o cadastro de Irmãos) **não foi criado ainda** — será adicionado como coluna nullable com chave estrangeira quando a tabela `irmaos` for criada na Fase 2 (ver `docs/MODULOS.md`).

### `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`

Geradas pelo `spatie/laravel-permission` (migration `create_permission_tables`). Guardam os perfis, permissões e seus vínculos com os usuários.

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

## Seeders

- `PerfilPermissaoSeeder`: cria o catálogo de permissões e os perfis iniciais.
- `AdministradorLocalSeeder`: cria/atualiza o administrador local a partir das variáveis `ADMIN_*` do `.env`. Nunca executa em produção (`App::environment('production')` é verificado explicitamente).

## Migrations relevantes

- `2026_07_29_130150_create_permission_tables` (spatie/laravel-permission)
- `2026_07_29_130231_adiciona_campos_administrativos_a_usuarios`
- `2026_07_29_130232_cria_tabela_auditorias`
