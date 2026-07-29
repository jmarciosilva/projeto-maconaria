# Arquitetura

## Visão geral

O sistema é um **monólito modular** em Laravel 13, com três áreas de navegação claramente separadas:

- **Site público** (`App\Http\Controllers\Site`, views em `resources/views/site`): sem autenticação, usa `x-layouts.site`.
- **Área restrita** (`App\Http\Controllers\AreaRestrita`, views em `resources/views/area-restrita`): usuários autenticados e verificados, usa `x-layouts.restrito`.
- **Painel administrativo** (`App\Http\Controllers\Admin`, views em `resources/views/admin`): usuários autenticados com permissões específicas, usa `x-layouts.admin`.

Um namespace `App\Http\Controllers\Api\V1` está reservado (vazio por enquanto) para a futura API consumida pelo aplicativo Flutter — ver `docs/API-FUTURA.md`.

## Sobre a estrutura `Application/` e `Domain/`

O escopo original do projeto sugere uma estrutura por camadas (`app/Application/<Modulo>`, `app/Domain/<Modulo>`) replicada para cada módulo de negócio (Cms, Blog, Tesouraria, Chancelaria etc.).

**Decisão:** essa estrutura não foi criada antecipadamente para módulos que ainda não existem (Tesouraria, Secretaria, Chancelaria, Documentos, Mural, CMS completo etc.). Criar pastas vazias como `app/Domain/Tesouraria/` sem nenhuma classe dentro seria uma abstração artificial sem benefício — e o próprio escopo original instrui a evitar isso (seção 8: *"não criar abstrações artificiais ou excesso de camadas sem necessidade"*).

Em vez disso, a abordagem adotada é:

1. Cada módulo começa simples: Controller enxuto + Form Request + Model/Policy, como no módulo de Usuários (`app/Http/Controllers/Admin/UsuarioController.php`).
2. Quando um módulo específico realmente precisar de uma camada de serviço/Action (regra de negócio não trivial, orquestração de múltiplas operações, necessidade de reuso entre Controller web e futura API), essa camada é criada **naquele momento**, sob `app/Domain/<Modulo>` ou `app/Support/`, e documentada aqui.
3. Isso garante que a arquitetura evolua alinhada à necessidade real de cada módulo, sem código morto.

## Camada de suporte transversal

- `App\Support\RegistradorDeAuditoria`: centraliza a criação de registros de auditoria (`App\Models\Auditoria`), para não espalhar essa lógica em cada Controller.
- `App\Enums`: enums de domínio (ex.: `StatusUsuario`). Novos enums de outros módulos seguem a mesma convenção.

## Autorização

- **Policies** (`App\Policies`) para regras de autorização ligadas a um Model específico (ex.: `UsuarioPolicy`).
- **Permissões nomeadas** (`spatie/laravel-permission`) para autorizações que não dependem de uma instância de Model (ex.: `$this->authorize('perfis.visualizar')`), usadas diretamente via Gate.
- O perfil **Superadministrador** tem bypass total via `Gate::before` em `AppServiceProvider`, para nunca ficar acidentalmente sem acesso.

Registro de policies não segue apenas a convenção automática de nomes do Laravel quando o nome da classe é traduzido para português (ex.: `UsuarioPolicy` para o model `User`) — nesses casos o registro é manual em `AppServiceProvider::boot()`.

## Diagramas

Ver `docs/DIAGRAMAS/`.
