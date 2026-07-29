# LGPD (Lei Geral de Proteção de Dados)

> Este documento não substitui uma análise jurídica formal. Itens marcados como
> **[PENDENTE DE VALIDAÇÃO JURÍDICA]** precisam ser revisados por um responsável
> legal/administrativo da Loja antes de decisões definitivas de retenção,
> exclusão ou compartilhamento de dados.

## Dados pessoais tratados até o momento

Nesta primeira entrega, o único cadastro de dados pessoais é o de **usuários do sistema** (`users`): nome, e-mail, telefone, status de acesso.

O cadastro de **Irmãos Maçons** (CPF, RG, endereço, datas de iniciação/elevação/exaltação, fotografia etc. — seção 9.3 do escopo) ainda não foi implementado (Fase 2 do roadmap). Quando for, este documento deve ser expandido com:

- Finalidade de cada campo sensível.
- Perfis com acesso a cada categoria de dado.
- Prazo de retenção após desligamento do Irmão.
- Processo de exportação/correção pelo próprio titular.
- Processo de anonimização/exclusão quando legalmente possível **[PENDENTE DE VALIDAÇÃO JURÍDICA]**.

## Princípios aplicados desde já

- **Minimização**: o cadastro de usuário atual não coleta nenhum dado além do estritamente necessário para autenticação e contato administrativo.
- **Controle de acesso**: dados de usuários só são visíveis a quem possui a permissão `usuarios.visualizar`.
- **Auditoria**: alterações em dados de usuários geram registro em `auditorias` (quem alterou, quando, de onde).
- **Nenhum dado sensível na área pública**: a landing page pública não expõe nenhum dado pessoal de usuários ou Irmãos.

## Retenção e exclusão

- Ainda não há política de retenção definida para os dados de usuários após desligamento/inativação **[PENDENTE DE VALIDAÇÃO JURÍDICA]**. Por ora, a desativação (`status = inativo`) é lógica, não há exclusão física de registros.
- Quando o cadastro de Irmãos for implementado, será necessário definir se e como aplicar exclusão física, anonimização, ou retenção obrigatória por exigência estatutária da Loja **[PENDENTE DE VALIDAÇÃO JURÍDICA]**.

## Resposta a incidentes

Processo formal de resposta a incidentes de segurança/vazamento ainda não definido **[PENDENTE DE VALIDAÇÃO JURÍDICA/ADMINISTRATIVA]** — a ser tratado na Fase 13 (Produção) do roadmap, junto com monitoramento e logs.
