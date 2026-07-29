# ADR 0001 — Não criar `Application/` e `Domain/` vazios por módulo antecipadamente

## Status

Aceita

## Contexto

O escopo original sugere uma estrutura de pastas por módulo (`app/Application/<Modulo>`, `app/Domain/<Modulo>`) replicada para todos os módulos de negócio (Cms, Blog, Tesouraria, Chancelaria, Secretaria, Documentos, Eventos, Galeria, Feed, Configurações), mesmo antes de qualquer um desses módulos ter regra de negócio implementada.

## Decisão

Não criar essas pastas para módulos que ainda não têm nenhuma classe real. A estrutura por camadas será criada **incrementalmente**, apenas quando um módulo específico precisar de uma camada de Service/Action (ex.: regra de negócio não trivial, necessidade de reuso entre Controller web e futura API).

## Consequências

- Evita diretórios vazios / arquivos `.gitkeep` sem função real.
- Evita divergência entre a estrutura "de referência" do escopo e o que de fato existe no código, o que confundiria mais do que ajudaria.
- Cada módulo novo deve, ao ser iniciado, avaliar se precisa dessa camada e criá-la deliberadamente — não por padrão.

## Referência

Ver `docs/ARQUITETURA.md`, seção "Sobre a estrutura `Application/` e `Domain/`".
