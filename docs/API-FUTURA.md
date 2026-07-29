# API Futura (integração com o aplicativo Flutter)

## Estratégia

O backend Laravel é a única fonte de verdade para regras de negócio. Controllers web (Blade) e a futura API **devem reutilizar as mesmas Actions/Services** quando essa camada existir para um módulo (ver `docs/ARQUITETURA.md`), evitando duplicar regra de negócio entre a web e a API.

## Autenticação

- **Web**: sessão (cookies), como já implementado via Laravel Breeze.
- **Mobile (futuro)**: [Laravel Sanctum](https://laravel.com/docs/sanctum) para tokens de API, autenticação separada da autenticação web. Sanctum **ainda não foi instalado** — será adicionado apenas quando o desenvolvimento da API for iniciado (Fase 12), para não introduzir dependências não utilizadas prematuramente.

## Versionamento

Namespace reservado: `App\Http\Controllers\Api\V1` (já criado, vazio). Futuras versões incompatíveis usarão `Api\V2` etc.

## Rotas planejadas

```text
/api/v1/auth
/api/v1/perfil
/api/v1/noticias
/api/v1/eventos
/api/v1/calendario
/api/v1/mural
/api/v1/documentos
```

Nenhuma dessas rotas existe ainda — serão adicionadas junto com cada módulo correspondente.

## Recursos e respostas

- Respostas serializadas via [API Resources](https://laravel.com/docs/eloquent-resources) do Laravel, não os Models diretamente.
- Paginação: paginação padrão do Laravel (`links`/`meta` no payload).
- Tratamento de erros: formato JSON consistente (`message`, `errors` quando aplicável), nunca stack traces em produção.

## Segurança

- Mesma base de Policies/permissões usada pela web será reutilizada pela API (a permissão não muda por canal de acesso).
- Dados restritos (Irmãos, Tesouraria, documentos privados) seguem as mesmas regras de autorização da web.

## Pendências

- Escolher estratégia de expiração/revogação de tokens Sanctum quando implementado.
- Definir rate limiting específico para a API mobile.
