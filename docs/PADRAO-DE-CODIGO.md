# Padrão de Código

## Idioma

- Classes de domínio, métodos de negócio e mensagens visíveis ao usuário: **português do Brasil** (ex.: `CriarNoticiaService::executar()`).
- Convenções estruturais do Laravel/PHP (nomes de Model, Controller, Request, rotas nomeadas) seguem o inglês quando é a convenção do framework (ex.: `User`, `Controller`, `FormRequest`).
- Tabelas e colunas: `snake_case`; nomes de domínio próprio em português (`usuarios`... quando aplicável), nomes de pacotes de terceiros mantidos como o pacote gera (`roles`, `permissions`).

## Estilo

- **Laravel Pint** (`pint.json`, preset `laravel`) — rodar com `composer format` antes de cada commit.
- `declare(strict_types=1)` é usado nas classes novas de domínio (Enums, Support, Policies, Seeders) sempre que praticável, mas não foi aplicado retroativamente a arquivos gerados pelo Breeze/Laravel para não gerar diffs desnecessários em código de terceiros.
- Controllers enxutos: autorizar → validar (via Form Request) → delegar para Action/Service quando existir → retornar Response/View.
- Não colocar regra de negócio em Blade views.
- Comentários apenas quando explicam o "porquê" (regra de negócio não óbvia, decisão arquitetural, contorno de limitação) — nunca o "o quê".

## Análise estática

- **Larastan** (`phpstan.neon.dist`, nível 5) — rodar com `composer analyse`.

## Testes

- `tests/Feature` para comportamento HTTP/integração; `tests/Unit` para lógica isolada.
- Banco de testes isolado (SQLite em memória, `phpunit.xml`), nunca o banco de desenvolvimento.
- Toda nova regra de autorização (Policy/permissão) deve ter um teste cobrindo o caso "sem permissão" e o caso "com permissão".

## Enums

Usar enums nativos do PHP para valores fixos de domínio, ex.:

```php
enum StatusUsuario: string
{
    case ATIVO = 'ativo';
    case INATIVO = 'inativo';
}
```

## Transações

Operações que alteram múltiplas tabelas usam `DB::transaction()`. Exceções de domínio específicas (ex.: `PeriodoFinanceiroFechadoException`) serão criadas quando os módulos correspondentes (Tesouraria etc.) forem implementados.
