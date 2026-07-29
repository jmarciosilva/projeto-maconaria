# LGPD (Lei Geral de Proteção de Dados)

> Este documento não substitui uma análise jurídica formal. Itens marcados como
> **[PENDENTE DE VALIDAÇÃO JURÍDICA]** precisam ser revisados por um responsável
> legal/administrativo da Loja antes de decisões definitivas de retenção,
> exclusão ou compartilhamento de dados.

## Dados pessoais tratados até o momento

### Usuários do sistema (`users`)

Nome, e-mail, telefone, status de acesso. Coleta mínima, necessária apenas para autenticação e contato administrativo.

### Irmãos Maçons (`irmaos`) — Fase 2

| Categoria | Campos | Finalidade | Quem acessa |
|---|---|---|---|
| Identificação civil | Nome completo, nome social, data de nascimento, CPF, RG | Identificação inequívoca do Irmão para fins administrativos e estatutários | Quem possui `irmaos.visualizar` |
| Contato | E-mail, telefone, endereço completo | Comunicação institucional e correspondência | Quem possui `irmaos.visualizar` |
| Dados maçônicos | CIM/matrícula, datas de iniciação/elevação/exaltação, grau, situação cadastral, cargo, data de ingresso/desligamento | Gestão da vida maçônica do Irmão na Loja | Quem possui `irmaos.visualizar` |
| Observações administrativas | Texto livre | Anotações internas da administração (ex.: pendências, ocorrências) | Quem possui `irmaos.visualizar` — **especialmente sensível, tratar com máximo cuidado ao editar** |
| Fotografia | Imagem | Identificação visual em documentos internos | Quem possui `irmaos.visualizar`, servida apenas por rota autenticada (nunca URL pública) |

Nenhum desses dados é exibido na área pública do site em nenhuma circunstância.

## Princípios aplicados desde já

- **Minimização**: os cadastros de usuário e de Irmão coletam apenas os campos descritos no escopo original, sem campos adicionais especulativos.
- **Controle de acesso**: dados de usuários e de Irmãos só são visíveis a quem possui a permissão correspondente (`usuarios.visualizar`, `irmaos.visualizar`).
- **Auditoria**: alterações relevantes em usuários e Irmãos geram registro em `auditorias` (quem alterou, quando, de onde); mudanças de grau/situação/cargo do Irmão também geram entrada dedicada em `irmao_historicos`.
- **Nenhum dado sensível na área pública**: a landing page pública não expõe nenhum dado pessoal de usuários ou Irmãos.
- **CPF protegido na serialização**: o campo `cpf` (e `rg`, `observacoes_administrativas`) está marcado como oculto (`#[Hidden]`) no Model `Irmao`, para nunca vazar acidentalmente em uma resposta JSON/array, mesmo que um desenvolvedor futuro serialize o Model sem cuidado.
- **Fotografia em armazenamento privado**: nunca fica acessível por URL pública/previsível.

## Retenção e exclusão

- Ainda não há política de retenção definida para os dados de usuários ou Irmãos após desligamento/inativação **[PENDENTE DE VALIDAÇÃO JURÍDICA]**. Por ora:
  - Usuários: desativação lógica (`status = inativo`), sem exclusão física.
  - Irmãos: exclusão lógica via soft delete (`deleted_at`), sem exclusão física — o registro pode ser necessário para fins estatutários/históricos da Loja mesmo após desligamento ou falecimento.
- Definir se e quando aplicar exclusão física, anonimização, ou retenção obrigatória por exigência estatutária da Loja **[PENDENTE DE VALIDAÇÃO JURÍDICA]**.
- Processo de exportação/correção de dados pelo próprio titular (o Irmão) ainda não implementado — pendente para uma fase futura.

## Resposta a incidentes

Processo formal de resposta a incidentes de segurança/vazamento ainda não definido **[PENDENTE DE VALIDAÇÃO JURÍDICA/ADMINISTRATIVA]** — a ser tratado na Fase 13 (Produção) do roadmap, junto com monitoramento e logs.
