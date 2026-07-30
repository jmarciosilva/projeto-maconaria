# API (integração com o aplicativo Flutter)

> Fase 12 implementada. Este documento deixou de ser "futuro" e passa a
> descrever a API V1 tal como construída — decisões e pendências reais, não
> mais planejadas.

## Estratégia

O backend Laravel é a única fonte de verdade para regras de negócio. Sempre
que uma regra de negócio já existia como Controller web e a API passou a
precisar dela também, a lógica foi extraída para uma classe de serviço em
`App\Support\<Módulo>\...` e reutilizada pelos dois lados — nunca duplicada.
Extraído nesta fase:

- `App\Support\Eventos\ConfirmadorPresencaEvento` — usado por
  `AreaRestrita\EventoController` (web) e `Api\V1\EventoController`.
- `App\Support\Mural\InteracaoMuralService` — usado por
  `Site\MuralInteracaoController` (web) e `Api\V1\MuralController`.
- `App\Support\Documentos\EnviadorDeEntrega` — usado por
  `Admin\DocumentoEntregaController` (web) e `Api\V1\DocumentoController`.

Endpoints somente leitura (notícias, eventos, mural) continuam com uma
consulta própria em cada Controller (Site/AreaRestrita/Admin/Api), seguindo
o mesmo padrão que já existia entre esses três namespaces antes da API — não
há regra de negócio real para extrair, só uma query.

## Autenticação

- **Web**: sessão (cookies), via Laravel Breeze.
- **Mobile**: [Laravel Sanctum](https://laravel.com/docs/sanctum) — **tokens
  de acesso pessoal** (`personal_access_tokens`), não o modo "SPA
  stateful/cookie" do Sanctum (esse modo é para front-ends na mesma raiz de
  domínio; o Flutter é só um cliente HTTP comum). Fluxo:
  1. `POST /api/v1/auth/login` com `email`, `password` e `device_name`
     (nome do dispositivo/instalação — vira o nome do token, para o usuário
     futuramente reconhecer/revogar sessões específicas). Retorna
     `{ token, usuario }`.
  2. Requisições seguintes enviam `Authorization: Bearer {token}`.
  3. `POST /api/v1/auth/logout` (autenticado) revoga *apenas* o token atual
     (`currentAccessToken()->delete()`), nunca todos os tokens do usuário.
- Mesma regra de negócio do login web (`App\Http\Requests\Auth\LoginRequest`)
  foi replicada em `App\Http\Requests\Api\V1\LoginApiRequest`: limite de 5
  tentativas por e-mail+IP e bloqueio de usuário inativo/bloqueado — não
  reutiliza a mesma classe porque o login web depende de sessão
  (`Auth::attempt`/`Auth::logout`), incompatível com o fluxo de token.
- O guard `sanctum` foi adicionado a `config/auth.php` (provider `users`,
  mesmo model `User`). As policies e permissões (spatie/laravel-permission)
  continuam funcionando sem nenhuma alteração: `HasRoles` resolve por
  `$guard_name` fixo (`web`) no Model, não pelo guard de autenticação HTTP.

## Versionamento

Namespace `App\Http\Controllers\Api\V1`. Futuras versões incompatíveis usarão
`Api\V2` etc. Rotas registradas em `routes/api.php`, prefixo `/api/v1`.

## Rotas implementadas

```text
POST   /api/v1/auth/login
POST   /api/v1/auth/logout                          (autenticado)
GET    /api/v1/perfil                                (autenticado)
GET    /api/v1/noticias
GET    /api/v1/noticias/{slug}
GET    /api/v1/eventos
GET    /api/v1/eventos/{slug}
GET    /api/v1/calendario
POST   /api/v1/eventos/{evento}/confirmar            (autenticado)
DELETE /api/v1/eventos/{evento}/confirmar            (autenticado, cancela)
GET    /api/v1/mural
GET    /api/v1/mural/{publicacao}
POST   /api/v1/mural/{publicacao}/comentarios        (autenticado)
POST   /api/v1/mural/{publicacao}/reacoes            (autenticado)
GET    /api/v1/documentos                            (autenticado)
GET    /api/v1/documentos/{atividade}                (autenticado)
POST   /api/v1/documentos/{atividade}/entregas        (autenticado)
GET    /api/v1/documentos/arquivos/{arquivo}          (autenticado)
```

## Suposições de visibilidade registradas

- **Notícias e Mural**: expostas na API com a **mesma visibilidade do site
  público** (`publicaNoSite()` / `scopePublico()`), independentemente de
  autenticação. Motivo: hoje não existe, na web, nenhuma superfície de
  leitura para conteúdo restrito desses dois módulos fora do painel
  administrativo (diferente de Eventos, que já tem uma área restrita
  própria e testada desde a Fase 5). Introduzir "notícia/mural restrito
  visível para autenticado" na API seria inventar uma regra de visibilidade
  sem um equivalente já validado na web — fica registrado como pendência
  para quando a web também ganhar essa superfície.
- **Eventos**: únicos com diferenciação por autenticação — visitante vê só
  `publicoNoSite()`; usuário autenticado vê `visivelNaAreaRestrita()`
  (público + restrito), mesma regra da área restrita web. As rotas de
  listagem/detalhe não exigem token, mas usam `$request->user('sanctum')`
  para reconhecer um usuário autenticado quando o token é enviado (o guard
  padrão da aplicação é `web`, que nunca reconhece um Bearer token).
- **Documentos e trabalhos**: sem conteúdo público — todas as rotas exigem
  token e reaproveitam as mesmas permissões do painel
  (`documentos.visualizar`, `documentos.enviar`) e a mesma
  `DocumentoArquivoPolicy::download` (autor da entrega ou
  `documentos.visualizar`).
- **Avaliação e comentários de atividades** (`documentos.avaliar`,
  comentários de atividade) **não foram expostos na API** nesta entrega —
  são ações de instrutor/avaliador, mais naturais no painel administrativo.
  Pendência para uma fase futura, se o app precisar delas.
- **Mural**: só leitura + comentar + reagir foram expostos. Criar
  publicações continua exclusivo do painel administrativo.

## Recursos e respostas

- Serialização via [API Resources](https://laravel.com/docs/eloquent-resources)
  (`App\Http\Resources\Api\V1\*`), nunca os Models diretamente — os Resources
  também são a camada que garante que campos sensíveis (senha, CPF/RG do
  Irmão, caminho físico de arquivo) nunca vazam na resposta.
- `JsonResource::withoutWrapping()` foi habilitado globalmente
  (`AppServiceProvider::boot()`): um recurso único (`show`, `perfil`) não
  vem embrulhado em `{"data": ...}`; coleções paginadas continuam com
  `data`/`links`/`meta` (o Laravel força esse envelope nelas
  independentemente dessa configuração).
- Erros: formato JSON consistente via `$exceptions->shouldRenderJsonWhen()`
  (já configurado em `bootstrap/app.php` desde o início do projeto para
  qualquer rota `api/*`) — nunca stack trace em produção.

## Segurança

- Mesma base de Policies/permissões da web (guard `web` no
  `HasRoles`/Spatie) é reutilizada pela API — a permissão não muda por
  canal de acesso.
- Dados restritos (Irmãos, documentos privados) seguem as mesmas regras de
  autorização da web; ver `IrmaoResumoResource` (nunca inclui CPF/RG) e
  `DocumentoArquivoPolicy`.

## Pendências

- Expiração/revogação de tokens Sanctum: hoje os tokens não expiram
  automaticamente (`sanctum.expiration` = null/config padrão). Definir uma
  política de expiração fica para quando o app estiver em uso real.
- Rate limiting específico para a API mobile (além do limite de tentativas
  de login) ainda não foi definido.
- Avaliação de entregas e comentários de atividades via API (ver acima).
- Visibilidade restrita de Notícias/Mural para usuários autenticados (ver
  acima) — depende de a web ganhar essa superfície primeiro.
