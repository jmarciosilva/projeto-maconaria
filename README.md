# Sistema Corporativo — ARLS Ferraz de Vasconcelos

## Sobre o projeto

Sistema corporativo da **Augusta e Respeitável Loja Simbólica Ferraz de Vasconcelos nº 2516 — Benfeitora da Ordem**, construído como um monólito modular em Laravel. O sistema substitui e amplia o site institucional atual, oferecendo:

1. Landing page pública administrável por CMS.
2. Painel administrativo corporativo.
3. Área restrita para usuários e Irmãos Maçons.
4. Módulos administrativos da Loja (Secretaria, Tesouraria, Chancelaria etc.).
5. Base arquitetural preparada para um futuro aplicativo em Flutter.

## Objetivos

- Centralizar a gestão institucional, documental, financeira e social da Loja.
- Separar claramente a área pública, a área restrita e o painel administrativo.
- Manter uma arquitetura desacoplada, testável e pronta para evoluir incrementalmente por módulos.

## Funcionalidades previstas

Consulte [`ROADMAP.md`](ROADMAP.md) para o detalhamento por fase e [`docs/MODULOS.md`](docs/MODULOS.md) para as decisões e suposições registradas em cada módulo.

## Stack tecnológica

| Camada | Tecnologia |
|---|---|
| Backend | PHP 8.3, Laravel 13 |
| Banco de dados | MySQL 8 (utf8mb4) |
| Views | Blade |
| CSS | Tailwind CSS v4 (via Vite) |
| Interatividade leve | Alpine.js |
| Build de assets | Vite |
| Autenticação | Laravel Breeze (stack Blade) |
| Autenticação da API (mobile) | Laravel Sanctum (tokens) |
| Papéis e permissões | spatie/laravel-permission |
| Testes | PHPUnit (via `php artisan test`) |
| Estilo de código | Laravel Pint |
| Análise estática | Larastan (PHPStan para Laravel) |
| Internacionalização | laravel-lang/lang + laravel-lang/publisher (pt_BR) |

### Dependências adicionadas e finalidade

| Pacote | Finalidade |
|---|---|
| `spatie/laravel-permission` | Perfis (roles) e permissões granulares usados em todo o sistema |
| `laravel/breeze` (dev) | Scaffolding de autenticação (login, recuperação de senha, verificação de e-mail) com Blade + Tailwind |
| `laravel/sanctum` | Autenticação por token da API (`/api/v1/*`), consumida pelo futuro app Flutter — ver `docs/API-FUTURA.md` |
| `laravel-lang/lang` + `laravel-lang/publisher` (dev) | Publicam as traduções pt_BR de validação, autenticação, paginação e senhas |
| `larastan/larastan` (dev) | Análise estática de código (PHPStan com extensões para Laravel) |
| `laravel/pint` (dev, já incluso no skeleton) | Formatação de código (PSR-12 / preset Laravel) |
| `mews/purifier` | Sanitização de HTML (HTMLPurifier) do conteúdo das páginas institucionais, editado via Quill.js |
| `quill` (npm) | Editor de texto rico (WYSIWYG) para o conteúdo das páginas institucionais no painel |
| `@tailwindcss/typography` (npm, dev) | Classe `.prose`, usada para estilizar o HTML das páginas institucionais no site público |

## Requisitos do ambiente

- Windows com [Laragon](https://laragon.org/) (Apache na porta 80, MySQL na porta 3306)
- PHP 8.3.30
- Composer 2.9+
- Node.js 22+ e npm 10+
- MySQL 8

## Instalação no Windows com Laragon

```powershell
cd D:\PROJETO-MACONARIA
composer install
copy .env.example .env
php artisan key:generate
```

## Configuração do banco MySQL

Crie o banco de dados (se ainda não existir):

```sql
CREATE DATABASE projeto_maconaria
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

## Configuração do arquivo .env

Copie `.env.example` para `.env` e ajuste conforme o seu ambiente. Principais chaves:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=projeto_maconaria
DB_USERNAME=root
DB_PASSWORD=

ADMIN_NAME="Administrador Local"
ADMIN_EMAIL="admin@localhost.test"
ADMIN_PASSWORD="alterar-senha"
```

As variáveis `ADMIN_*` são usadas exclusivamente pelo `AdministradorLocalSeeder` em ambiente de desenvolvimento — nunca em produção.

## Instalação das dependências PHP

```powershell
composer install
```

## Instalação das dependências JavaScript

```powershell
npm install
```

## Execução das migrations

```powershell
php artisan migrate
```

## Execução dos seeders

```powershell
php artisan db:seed
```

Isso cria os perfis/permissões iniciais (`PerfilPermissaoSeeder`) e o administrador local (`AdministradorLocalSeeder`), com o perfil **Superadministrador**.

## Compilação dos assets

```powershell
npm run build
```

Para desenvolvimento com recarregamento automático:

```powershell
npm run dev
```

## Execução dos testes

```powershell
php artisan test
```

Os testes usam SQLite em memória (configurado em `phpunit.xml`), isolados do banco MySQL de desenvolvimento.

## Análise estática

```powershell
composer analyse
```

Executa o Larastan (PHPStan) com o nível configurado em `phpstan.neon.dist`.

## Formatação do código

```powershell
composer format
```

Executa o Laravel Pint (preset `laravel`) sobre `app/`, `database/`, `routes/` e `tests/`.

## Estrutura do projeto

```text
app/
├── Enums/                  # Enums de domínio (ex.: StatusUsuario)
├── Http/
│   ├── Controllers/
│   │   ├── Admin/          # Painel administrativo
│   │   ├── AreaRestrita/   # Área restrita a usuários autenticados
│   │   ├── Auth/           # Autenticação (gerado pelo Breeze)
│   │   ├── Api/V1/         # API consumida pelo app Flutter (Fase 12)
│   │   └── Site/           # Páginas públicas
│   ├── Requests/
│   │   ├── Admin/          # Form Requests do painel administrativo
│   │   ├── Api/V1/         # Form Requests da API
│   │   └── Auth/           # Form Requests de autenticação
│   └── Resources/
│       └── Api/V1/         # API Resources (serialização das respostas da API)
├── Models/
├── Policies/
├── Providers/
├── Support/                # Serviços transversais (ex.: RegistradorDeAuditoria)
│   ├── Documentos/         # Reaproveitado pela web e pela API (ex.: EnviadorDeEntrega)
│   ├── Eventos/            # Reaproveitado pela web e pela API (ex.: ConfirmadorPresencaEvento)
│   └── Mural/              # Reaproveitado pela web e pela API (ex.: InteracaoMuralService)
resources/views/
├── components/
│   ├── layouts/            # x-layouts.site, x-layouts.admin, x-layouts.restrito
│   └── ui/                 # Componentes reutilizáveis (botão, tabela, alerta etc.)
├── site/                   # Views públicas
├── area-restrita/          # Views da área restrita
├── admin/                  # Views do painel administrativo
└── auth/, profile/         # Views de autenticação e perfil (Breeze)
```

A estrutura por camadas (`Application/` e `Domain/` por módulo) sugerida no escopo original será adotada de forma incremental, apenas quando um módulo específico realmente precisar dela — ver [`docs/ARQUITETURA.md`](docs/ARQUITETURA.md).

## Perfis e permissões

Perfis iniciais: Superadministrador, Administrador, Venerável Mestre, Secretário, Tesoureiro, Chanceler, Bibliotecário, Editor de Conteúdo, Instrutor, Irmão, Visitante Autorizado.

O perfil **Superadministrador** sempre tem acesso total via `Gate::before` (`app/Providers/AppServiceProvider.php`), independentemente das permissões atribuídas a ele.

O mapeamento completo perfil → permissões está em `database/seeders/PerfilPermissaoSeeder.php` e documentado como suposição em [`docs/MODULOS.md`](docs/MODULOS.md).

## Segurança

Ver [`docs/SEGURANCA.md`](docs/SEGURANCA.md).

## Preparação para Flutter

Ver [`docs/API-FUTURA.md`](docs/API-FUTURA.md).

## Roadmap

Ver [`ROADMAP.md`](ROADMAP.md).

## Estratégia de branches

- `main`: branch principal, sempre estável.
- `feature/<nome>`: uma branch por funcionalidade, criada apenas quando a funcionalidade é iniciada.

## Padrão de commits

```text
chore: configura estrutura inicial do projeto
docs: cria documentação inicial
feat: adiciona autenticação de usuários
fix: corrige autorização de acesso à tesouraria
test: adiciona testes de permissões
refactor: desacopla criação de notícias
style: ajusta layout responsivo do painel
```

## Licença e uso institucional

Uso interno e institucional da Augusta e Respeitável Loja Simbólica Ferraz de Vasconcelos nº 2516 — Benfeitora da Ordem. Não distribuído publicamente.
