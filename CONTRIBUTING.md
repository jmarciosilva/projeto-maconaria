# Como contribuir

## Idioma

Todo o código, documentação, comentários e mensagens visíveis ao usuário devem ser escritos em português do Brasil. Nomes técnicos (classes, métodos, variáveis) seguem as convenções do Laravel/PHP.

## Branches

- `main`: branch principal, sempre estável e implantável.
- `feature/<nome-da-funcionalidade>`: criada apenas quando a funcionalidade correspondente é iniciada (ver `ROADMAP.md`).

Não crie branches para todos os módulos antecipadamente.

## Commits

Utilize o padrão:

```text
chore: configura estrutura inicial do projeto
docs: cria documentação inicial
feat: adiciona autenticação de usuários
fix: corrige autorização de acesso à tesouraria
test: adiciona testes de permissões
refactor: desacopla criação de notícias
style: ajusta layout responsivo do painel
```

Commits devem ser pequenos, coerentes e nunca incluir `.env`, credenciais ou arquivos temporários.

## Antes de cada commit

```powershell
php artisan test
composer format
composer analyse
```

Não marque uma tarefa do `ROADMAP.md` como concluída sem testes passando e documentação atualizada.

## Pull requests

- Descreva o que foi alterado e por quê.
- Relacione a fase do `ROADMAP.md` correspondente.
- Garanta que os testes e a análise estática estejam passando.

## Decisões arquiteturais

Decisões não triviais devem ser registradas em `docs/DECISOES/` (ADRs) e suposições sobre regras de negócio ainda não definidas devem ser registradas em `docs/MODULOS.md`.
