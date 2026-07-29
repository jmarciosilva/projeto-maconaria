# Diagrama — Arquitetura Atual (Fundação)

```mermaid
flowchart TB
    subgraph Publico["Site público (sem autenticação)"]
        SiteController["Site\PaginaInicialController"] --> SiteHome["site/home.blade.php<br/>x-layouts.site"]
    end

    subgraph Auth["Autenticação (Breeze)"]
        Login["Auth\AuthenticatedSessionController"]
        Senha["Auth\PasswordResetLinkController<br/>NewPasswordController<br/>PasswordController"]
        Verificacao["Auth\EmailVerification*Controller"]
    end

    subgraph Restrita["Área restrita (auth + verified)"]
        Painel["AreaRestrita\PainelController"] --> PainelView["area-restrita/painel.blade.php<br/>x-layouts.restrito"]
        Perfil["AreaRestrita\ProfileController"] --> PerfilView["profile/edit.blade.php<br/>x-layouts.restrito"]
    end

    subgraph Admin["Painel administrativo (auth + verified + permissão)"]
        Usuarios["Admin\UsuarioController"] --> UsuariosView["admin/usuarios/*<br/>x-layouts.admin"]
        Perfis["Admin\PerfilController"] --> PerfisView["admin/perfis/index<br/>x-layouts.admin"]
    end

    subgraph Dominio["Modelos e suporte"]
        User["Models\User<br/>HasRoles, MustVerifyEmail"]
        Auditoria["Models\Auditoria"]
        Registrador["Support\RegistradorDeAuditoria"]
        StatusEnum["Enums\StatusUsuario"]
    end

    subgraph Autorizacao["Autorização"]
        Policy["Policies\UsuarioPolicy"]
        Gate["Gate::before<br/>(bypass Superadministrador)"]
        Spatie["spatie/laravel-permission<br/>roles, permissions"]
    end

    Login -.-> User
    Usuarios --> Policy
    Usuarios --> Registrador --> Auditoria
    Policy --> Spatie
    User --> Spatie
    User --> Gate
    User --> StatusEnum
```

## Notas

- O namespace `App\Http\Controllers\Api\V1` existe reservado, mas ainda não tem rotas nem controllers — ver `docs/API-FUTURA.md`.
- Módulos futuros (Tesouraria, Secretaria, Chancelaria, CMS completo, Notícias, Eventos, Documentos, Mural, Galeria) ainda não têm representação neste diagrama — serão adicionados incrementalmente conforme forem implementados.
