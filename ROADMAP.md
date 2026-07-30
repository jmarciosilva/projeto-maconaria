# Roadmap do Projeto

## Legenda

- [ ] Não iniciado
- [x] Concluído
- [ ] 🚧 Em andamento
- [ ] ⏸️ Pausado
- [ ] ⚠️ Bloqueado

## Fase 0 — Fundação

- [x] Validar ambiente
- [x] Instalar Laravel
- [x] Configurar MySQL
- [x] Configurar Tailwind CSS
- [x] Configurar autenticação
- [x] Criar estrutura de documentação
- [x] Configurar testes
- [x] Configurar Laravel Pint
- [x] Configurar análise estática
- [x] Criar primeiro seed administrativo

## Fase 1 — Autenticação e acessos

- [x] Login
- [x] Logout
- [x] Recuperação de senha
- [x] Alteração de senha
- [ ] Alteração de e-mail (com confirmação e auditoria — pendente, ver `docs/MODULOS.md`)
- [ ] ⚠️ Verificação de e-mail (scaffolding pronta, mas **temporariamente desativada** — não há SMTP configurado ainda; ver `docs/MODULOS.md`)
- [x] Usuários (CRUD básico, ativar/desativar/bloquear/desbloquear)
- [x] Perfis (seed inicial; CRUD pelo painel pendente)
- [x] Permissões
- [x] Policies
- [x] Auditoria inicial

## Fase 2 — Cadastro de Irmãos

- [x] Cadastro
- [x] Histórico
- [x] Graus
- [x] Cargos (campo de texto livre — ver `docs/MODULOS.md`)
- [x] Situação cadastral
- [x] Usuário vinculado
- [x] Proteção de dados

## Fase 3 — CMS e landing page

- [x] Configurações institucionais
  - [x] Logotipo da Loja (upload pelo painel)
  - [x] Nome da Loja, título/subtítulo institucional
  - [x] Telefone público
  - [x] Endereço (usado no rodapé)
  - [x] E-mail institucional
  - [x] Redes sociais (Facebook, Instagram, Twitter/X, TikTok)
  - [ ] Mapa de localização
- [ ] Menu (fixo por enquanto — links institucionais adicionados diretamente no layout; reordenar/ocultar pelo painel fica para uma fase futura)
- [x] Carrossel (CRUD completo, imagem desktop/mobile, período de exibição, ordem)
- [x] 🚧 Página inicial (carrossel dinâmico e hero configurável funcionais; notícias e outras seções ainda pendentes)
- [x] Sobre nós
- [x] O que é Maçonaria
- [x] Maçonaria para Jovens
- [x] Como a Maçonaria pode mudar um cidadão
- [x] Rodapé (endereço, telefone, e-mail, redes sociais e links de política de privacidade/termos de uso)
- [x] 🚧 SEO (meta título/descrição por página institucional; canonical URL, imagem de compartilhamento e sitemap ainda pendentes)

## Fase 4 — Notícias e conteúdo

- [x] Categorias
- [x] Tags
- [x] Notícias
- [x] Fluxo editorial
- [x] Agendamento
- [x] Destaques
- [x] Histórico de versões

## Fase 5 — Eventos e calendário

- [ ] Eventos
- [ ] Sessões
- [ ] Calendário
- [ ] Confirmação de presença
- [ ] Exibição pública e restrita

## Fase 6 — Secretaria

- [ ] Atas
- [ ] Versões
- [ ] Aprovação
- [ ] Correspondências
- [ ] Documentos oficiais
- [ ] Numeração

## Fase 7 — Chancelaria

- [ ] Frequência
- [ ] Presenças
- [ ] Ausências
- [ ] Visitantes
- [ ] Comunicados
- [ ] Relatórios

## Fase 8 — Tesouraria

- [ ] Categorias
- [ ] Contas
- [ ] Receitas
- [ ] Despesas
- [ ] Mensalidades
- [ ] Pagamentos
- [ ] Recebimentos
- [ ] Aprovações
- [ ] Relatórios
- [ ] Fechamento
- [ ] Auditoria financeira

## Fase 9 — Documentos e trabalhos

- [ ] Atividades
- [ ] Entregas
- [ ] Avaliações
- [ ] Comentários
- [ ] Arquivos privados
- [ ] Policies de download

## Fase 10 — Galeria e Mural da Loja

- [ ] Álbuns
- [ ] Fotografias
- [ ] Publicações
- [ ] Comentários
- [ ] Reações
- [ ] Moderação
- [ ] Visibilidade

## Fase 11 — Configurações de e-mail

- [ ] Cadastro SMTP
- [ ] Criptografia
- [ ] Aplicação dinâmica
- [ ] Teste de envio
- [ ] Auditoria
- [ ] Tratamento de falhas

## Fase 12 — API e Flutter

- [ ] Definir API V1
- [ ] Configurar autenticação mobile
- [ ] Criar documentação
- [ ] Criar API Resources
- [ ] Notícias
- [ ] Eventos
- [ ] Mural
- [ ] Perfil
- [ ] Documentos

## Fase 13 — Produção

- [ ] Revisar segurança
- [ ] Revisar LGPD
- [ ] Configurar filas
- [ ] Configurar scheduler
- [ ] Configurar backups
- [ ] Configurar logs
- [ ] Configurar monitoramento
- [ ] Executar testes
- [ ] Homologação
- [ ] Deploy
