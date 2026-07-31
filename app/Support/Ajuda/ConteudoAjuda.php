<?php

declare(strict_types=1);

namespace App\Support\Ajuda;

/**
 * Texto de ajuda contextual mostrado no modal acionado pelo botão "Ajuda"
 * do painel administrativo. O conteúdo é escolhido pelo nome da rota atual
 * (prefixo) — quando nenhuma entrada específica bate, cai no bloco genérico.
 *
 * A ordem do mapa() importa: prefixos mais específicos (ex.: um submódulo)
 * precisam vir antes do prefixo geral do módulo (ex.: "admin.tesouraria."),
 * já que paraRota() retorna no primeiro prefixo que bater.
 */
final class ConteudoAjuda
{
    /**
     * @return array{titulo: string, resumo?: string, itens: array<int, string>, exemplos?: array<int, string>}
     */
    public static function paraRota(?string $nomeRota): array
    {
        if ($nomeRota !== null) {
            foreach (self::mapa() as [$prefixos, $conteudo]) {
                foreach ($prefixos as $prefixo) {
                    if ($nomeRota === $prefixo || str_starts_with($nomeRota, $prefixo)) {
                        return $conteudo;
                    }
                }
            }
        }

        return self::generico();
    }

    /**
     * @return array<int, array{0: array<int, string>, 1: array{titulo: string, resumo?: string, itens: array<int, string>, exemplos?: array<int, string>}}>
     */
    private static function mapa(): array
    {
        return [
            [['admin.dashboard'], [
                'titulo' => 'Painel',
                'resumo' => 'Esta é a tela inicial do painel: reúne, em cartões, os principais números do sistema, sem precisar visitar cada módulo separadamente.',
                'itens' => [
                    'Esta tela reúne os principais números do sistema: usuários, tesouraria, chancelaria e conteúdo do site.',
                    'Os grupos de cartões exibidos dependem das permissões do seu perfil — só aparece o que você tem acesso a visualizar.',
                    'Os números de tesouraria consideram apenas lançamentos já baixados; os de chancelaria consideram os últimos 3 meses.',
                    'Use os links "Ver tesouraria →" e "Ver chancelaria →" em cada bloco para abrir o módulo completo.',
                ],
                'exemplos' => [
                    'Um usuário com perfil Tesoureiro vê os cartões de Usuários e Tesouraria, mas não os de Chancelaria, se não tiver essa permissão.',
                ],
            ]],
            [['admin.usuarios.'], [
                'titulo' => 'Usuários',
                'resumo' => 'Controla quem pode acessar o painel administrativo e a Área Restrita do site, e com quais perfis de permissão.',
                'itens' => [
                    'Lista as contas com acesso ao sistema: nome, e-mail, status, perfis e último acesso.',
                    '"Novo usuário" cria uma conta e define os perfis de acesso dela.',
                    '"Editar" altera dados cadastrais e perfis. "Ativar/Desativar" controla se o usuário consegue fazer login. "Bloquear/Desbloquear" é usado em caso de suspeita de uso indevido da conta.',
                    'Por segurança, você não pode desativar nem bloquear a própria conta.',
                ],
                'exemplos' => [
                    'Um Irmão eleito Secretário recebe uma conta com o perfil "Secretário", que libera o módulo de Secretaria para ele.',
                ],
            ]],
            [['admin.perfis.'], [
                'titulo' => 'Perfis e Permissões',
                'resumo' => 'Esta tela é somente consulta: mostra os perfis de acesso existentes e as permissões que cada um concede. Para dar um perfil a alguém, use a tela de Usuários.',
                'itens' => [
                    'Cada perfil listado mostra, em badges, as permissões que ele concede no sistema.',
                    'O perfil Superadministrador tem acesso total sempre, mesmo quando a lista de permissões dele aparece vazia.',
                    'Para dar ou tirar um perfil de alguém, acesse Usuários → Editar e marque os perfis desejados naquele formulário.',
                    'A criação e edição de novos perfis pelo painel ainda não está disponível nesta versão do sistema.',
                ],
                'exemplos' => [
                    'Perfis já cadastrados: Superadministrador, Administrador, Venerável Mestre, Secretário, Tesoureiro, Chanceler, Bibliotecário, Editor de Conteúdo, Instrutor, Irmão e Visitante Autorizado.',
                ],
            ]],
            [['admin.irmaos.'], [
                'titulo' => 'Irmãos',
                'resumo' => 'Cadastro completo dos membros da Loja: dados pessoais, endereço, percurso maçônico e, opcionalmente, o login vinculado a cada um.',
                'itens' => [
                    'Preencha os Dados pessoais e, ao digitar o CEP, o endereço é completado automaticamente.',
                    'Em Dados maçônicos, registre o grau atual, a situação cadastral e as datas de iniciação, elevação, exaltação e ingresso na Loja.',
                    'Toda mudança de grau, situação cadastral ou cargo fica registrada automaticamente no histórico do Irmão, com data e responsável — não é preciso anotar isso manualmente.',
                    'Em Acesso e fotografia, vincule um usuário do sistema ao Irmão para que ele possa fazer login na Área Restrita.',
                    '"Remover" não apaga o cadastro definitivamente — o registro pode ser recuperado, mas deixa de aparecer no sistema.',
                ],
                'exemplos' => [
                    'Graus possíveis: Aprendiz, Companheiro, Mestre. Situações cadastrais: Ativo, Inativo, Licenciado, Irregular, Desligado, Falecido.',
                    'Um Irmão iniciado em 2020 e elevado em 2022 fica com Grau atual "Companheiro", e as duas datas registradas no histórico.',
                ],
            ]],
            [['admin.configuracoes.email.'], [
                'titulo' => 'Configurações de E-mail',
                'resumo' => 'Define o servidor SMTP usado pelo sistema para enviar e-mails automáticos, como a confirmação do formulário de contato.',
                'itens' => [
                    'Preencha host, porta, usuário, senha e criptografia fornecidos pelo seu provedor de e-mail.',
                    'Deixar o campo Senha em branco ao editar mantém a senha já salva anteriormente — não é preciso digitá-la de novo a cada alteração.',
                    'O mailer "Log" apenas grava os e-mails em arquivo, sem enviar de verdade — use somente em ambiente de testes.',
                    'Depois de salvar, use "Enviar e-mail de teste" informando um destinatário para confirmar que o envio está funcionando antes de ativar em produção.',
                ],
                'exemplos' => [
                    'Gmail: host smtp.gmail.com, porta 587, criptografia TLS, usando uma senha de aplicativo gerada nas configurações da conta Google.',
                ],
            ]],
            [['admin.configuracoes.institucional.'], [
                'titulo' => 'Configurações do Site',
                'resumo' => 'Dados que alimentam o cabeçalho, o rodapé e a home do site público — alterações aqui refletem imediatamente para os visitantes.',
                'itens' => [
                    'Em Identidade institucional, defina o nome da Loja e os textos de destaque exibidos na página inicial.',
                    'Em Logotipo, envie o selo da Loja — ele aparece no cabeçalho, no rodapé e no favicon do site.',
                    'Em Contato e rodapé, o endereço informado também é usado para montar os links de rota do Waze e do Google Maps no rodapé.',
                    'Em Redes sociais, deixe um campo em branco para ocultar aquele ícone no rodapé do site.',
                ],
                'exemplos' => [
                    'Preencher o endereço completo (rua, número, bairro, cidade e UF) garante que os botões de rota do rodapé abram a localização correta.',
                ],
            ]],
            [['admin.carrossel.'], [
                'titulo' => 'Carrossel da Página Inicial',
                'resumo' => 'Gerencia as imagens em destaque exibidas no topo da home do site público.',
                'itens' => [
                    'Cada item tem uma imagem para desktop (obrigatória) e outra para mobile (opcional — se vazia, a versão desktop é reaproveitada).',
                    'A Ordem de exibição define a sequência dos slides. "Exibir a partir de"/"até" programa um item para aparecer só durante um período específico.',
                    'Itens marcados como Inativo ficam guardados no sistema, mas somem do carrossel do site.',
                ],
                'exemplos' => [
                    'Um slide de "7 de Setembro" pode ficar programado para aparecer só entre 01/09 e 08/09, sem precisar removê-lo manualmente depois.',
                ],
            ]],
            [['admin.paginas-institucionais.'], [
                'titulo' => 'Páginas Institucionais',
                'resumo' => 'Gerencia o conteúdo das páginas fixas do site público, como Sobre Nós, Nossa História e Política de Privacidade.',
                'itens' => [
                    'Seis páginas têm URL fixa, definida no escopo do projeto — para essas, o campo Slug fica bloqueado para edição.',
                    'Use o editor de Conteúdo para formatar o texto com títulos, listas e negrito, exatamente como ele vai aparecer no site.',
                    'Uma página só fica visível para o público quando marcada como Publicado.',
                ],
                'exemplos' => [
                    'As páginas de URL fixa são: Sobre Nós, Nossa História, O que é Maçonaria, Maçonaria para Jovens, Mudando o Cidadão, Política de Privacidade e Termos de Uso.',
                ],
            ]],
            [['admin.chancelaria.frequencias.'], [
                'titulo' => 'Frequências',
                'resumo' => 'Registra a presença dos Irmãos em cada sessão ou evento da Loja.',
                'itens' => [
                    'Primeiro escolha o evento na lista — os mais recentes aparecem primeiro.',
                    'Em seguida, marque para cada Irmão o status: Presente, Ausente ou Justificado, com uma observação opcional.',
                    'Deixar o status de um Irmão em branco remove a marcação dele para aquele evento, caso já exista uma.',
                ],
                'exemplos' => [
                    'Na sessão do dia 06/08, marcar 18 Irmãos como Presente e 2 como Justificado, com a observação "viagem a trabalho".',
                ],
            ]],
            [['admin.chancelaria.visitantes.'], [
                'titulo' => 'Visitantes',
                'resumo' => 'Registra Irmãos de outras Lojas que compareceram a uma sessão ou evento da Loja.',
                'itens' => [
                    'Cadastre nome, Loja e potência de origem do visitante.',
                    'Vincular o visitante a um evento é opcional, mas ajuda a manter o histórico organizado por sessão.',
                ],
                'exemplos' => [
                    'Visitante "Fulano de Tal", Loja "Estrela do Oriente", potência GOSP, vinculado à sessão de 13/08.',
                ],
            ]],
            [['admin.chancelaria.comunicados.'], [
                'titulo' => 'Comunicados da Chancelaria',
                'resumo' => 'Registra avisos formais emitidos pela Chancelaria para os Irmãos.',
                'itens' => [
                    'Escreva o comunicado no editor de texto rico, com título e conteúdo formatado.',
                    'Um comunicado só fica disponível para os Irmãos quando o status é alterado para Publicado — a data de publicação é preenchida automaticamente.',
                ],
                'exemplos' => [
                    'Comunicado "Alteração no horário das sessões de agosto", publicado uma semana antes da mudança entrar em vigor.',
                ],
            ]],
            [['admin.chancelaria.'], [
                'titulo' => 'Chancelaria — como funciona',
                'resumo' => 'Este módulo controla a frequência dos Irmãos às sessões, os visitantes recebidos e os comunicados formais da Loja.',
                'itens' => [
                    'Os cartões desta tela resumem as presenças, ausências e justificativas dos últimos 3 meses.',
                    'Use "Registrar frequência" para marcar a presença dos Irmãos em um evento específico.',
                    'Use "Visitantes" para registrar Irmãos de outras Lojas que compareceram a uma sessão.',
                    'Use "Comunicados" para publicar avisos formais da Chancelaria.',
                ],
                'exemplos' => [
                    'Depois de cada sessão, registre a frequência dos Irmãos presentes antes de encerrar o expediente da Chancelaria.',
                ],
            ]],
            [['admin.noticias.', 'admin.noticia-categorias.', 'admin.noticia-tags.'], [
                'titulo' => 'Notícias',
                'resumo' => 'Controla as notícias publicadas no site público, incluindo rascunhos, agendamentos e destaque na home.',
                'itens' => [
                    'Gerencia as notícias publicadas no site público, além das categorias e tags usadas para organizá-las.',
                    'Uma notícia só aparece no site quando está com status "Publicada" e visibilidade pública.',
                    '"Categorias" e "Tags" ajudam os visitantes a filtrar e encontrar notícias relacionadas.',
                ],
                'exemplos' => [
                    'Uma notícia com status "Agendada" e "Publicado em" definido para uma data futura aparece no site automaticamente nessa data.',
                ],
            ]],
            [['admin.eventos.'], [
                'titulo' => 'Eventos',
                'resumo' => 'Controla a agenda pública da Loja: sessões, eventos abertos e confirmações de presença pela Área Restrita.',
                'itens' => [
                    'Cadastra sessões e eventos da Loja, exibidos publicamente em "Eventos" e no "Calendário" do site.',
                    '"Calendário" mostra a visão mensal de todos os eventos cadastrados.',
                    'Eventos com visibilidade pública aparecem no site institucional; os demais ficam restritos à Área Restrita.',
                ],
                'exemplos' => [
                    'Um jantar ritualístico com "Permitir confirmação de presença" ativado deixa os Irmãos confirmarem presença pela Área Restrita, respeitando a capacidade de vagas definida.',
                ],
            ]],
            [['admin.mural.'], [
                'titulo' => 'Mural',
                'resumo' => 'Controla o mural social do site, incluindo a moderação de comentários enviados pelos usuários autenticados.',
                'itens' => [
                    'Gerencia as publicações do mural social, exibido publicamente no site.',
                    'Comentários enviados por usuários passam por aprovação antes de aparecerem publicamente — use "Aprovar" para liberá-los.',
                    'Curtidas (reações) são registradas automaticamente pelos usuários autenticados e não precisam de moderação.',
                ],
                'exemplos' => [
                    'Uma foto do último evento social publicada no mural recebe comentários de Irmãos, que ficam pendentes de aprovação até serem revisados.',
                ],
            ]],
            [['admin.galeria.'], [
                'titulo' => 'Galeria',
                'resumo' => 'Controla os álbuns de fotos exibidos na Galeria pública do site.',
                'itens' => [
                    'Gerencia os álbuns de fotos exibidos na Galeria pública do site.',
                    'Cada álbum reúne várias fotografias; publique o álbum para que ele passe a aparecer no site.',
                ],
                'exemplos' => [
                    'Um álbum "Comemoração de 7 de Setembro" com 20 fotos, publicado com visibilidade pública, aparece na Galeria do site.',
                ],
            ]],
            [['admin.secretaria.'], [
                'titulo' => 'Secretaria',
                'resumo' => 'Controla atas, correspondências e documentos oficiais da Loja, com numeração e fluxo de aprovação.',
                'itens' => [
                    'Gerencia documentos institucionais da secretaria (atas, editais, comunicados administrativos).',
                    'Um documento segue o fluxo rascunho → aprovado → publicado. Use "Aprovar" e "Publicar" para avançar.',
                    'Documentos publicados ficam disponíveis para os Irmãos na Área Restrita.',
                ],
                'exemplos' => [
                    'Uma ata de sessão é criada como Rascunho, aprovada pelo Secretário e depois Publicada, ficando disponível para consulta na Área Restrita.',
                ],
            ]],
            [['admin.documentos.entregas.'], [
                'titulo' => 'Entregas',
                'resumo' => 'Uma entrega é o envio feito por um Irmão em resposta a uma atividade proposta pela administração.',
                'itens' => [
                    'Cada entrega tem título, descrição e arquivos anexados pelo próprio Irmão.',
                    'O status caminha de Enviada para Em avaliação e, depois do parecer, para Avaliada — ou Devolvida, se precisar de ajustes.',
                    '"Avaliar" registra o parecer do responsável sobre uma entrega específica e muda o status dela para Avaliada.',
                ],
                'exemplos' => [
                    'Um Irmão envia sua entrega para o trabalho "Simbolismo do Esquadro e Compasso"; o instrutor avalia e deixa um parecer.',
                ],
            ]],
            [['admin.documentos.'], [
                'titulo' => 'Documentos e Trabalhos',
                'resumo' => 'Gerencia atividades propostas aos Irmãos, as entregas recebidas, avaliações e comentários de acompanhamento.',
                'itens' => [
                    'Uma atividade é um trabalho ou tarefa proposta pela administração, com título, descrição, anexos e status Rascunho, Publicada ou Encerrada.',
                    'Publique a atividade para que os Irmãos possam vê-la e enviar suas entregas pela Área Restrita.',
                    'Abra uma atividade para ver as entregas recebidas, avaliá-las em "Entregas" e trocar comentários com os participantes — comentários não mudam o status de nada, servem só para discussão.',
                ],
                'exemplos' => [
                    'Atividade "Resenha sobre o Grau de Aprendiz", publicada com prazo de entrega, recebendo entregas de vários Irmãos para avaliação individual.',
                ],
            ]],
            [['admin.tesouraria.categorias.'], [
                'titulo' => 'Categorias Financeiras',
                'resumo' => 'As categorias organizam os lançamentos por tipo, separando receitas de despesas nos totais e relatórios da Tesouraria.',
                'itens' => [
                    'Cadastre uma categoria para cada tipo de movimentação recorrente da Loja.',
                    'Toda categoria tem um Tipo fixo — Receita ou Despesa — escolha com atenção, pois é o que define em qual total ela entra.',
                    'Categorias marcadas como inativas somem das opções ao criar um novo lançamento, mas continuam aparecendo nos lançamentos antigos.',
                ],
                'exemplos' => [
                    'Categorias de receita: "Mensalidades", "Doações", "Eventos".',
                    'Categorias de despesa: "Aluguel", "Água e Luz", "Manutenção do Templo".',
                ],
            ]],
            [['admin.tesouraria.contas.'], [
                'titulo' => 'Contas Financeiras',
                'resumo' => 'As contas representam onde o dinheiro da Loja fica guardado. Todo lançamento é vinculado a uma conta.',
                'itens' => [
                    'Cadastre uma conta para cada caixa físico ou conta bancária que a Loja movimenta.',
                    'O campo Saldo inicial é o valor que já existia na conta antes de começar a usar o sistema — os lançamentos baixados são somados ou subtraídos a partir dele.',
                ],
                'exemplos' => [
                    '"Caixa da Loja" (tipo Caixa) — dinheiro em espécie guardado fisicamente.',
                    '"Banco do Brasil — Conta Corrente" (tipo Banco), com saldo inicial de R$ 1.500,00.',
                ],
            ]],
            [['admin.tesouraria.lancamentos.'], [
                'titulo' => 'Lançamentos Financeiros',
                'resumo' => 'Aqui ficam todas as receitas e despesas da Loja. Cada lançamento passa por um fluxo de aprovação antes de contar no saldo.',
                'itens' => [
                    '"Novo lançamento" registra uma receita ou despesa, sempre vinculada a uma categoria e a uma conta.',
                    'O lançamento nasce como Rascunho, pode virar Pendente, depois Aprovado por quem tem permissão e, por fim, Baixado quando o valor realmente entra ou sai da conta.',
                    'Somente lançamentos com status Baixado entram no saldo mostrado na Tesouraria e no Painel.',
                ],
                'exemplos' => [
                    'Despesa "Manutenção do ar-condicionado", R$ 180,00, categoria Manutenção do Templo, conta Caixa da Loja: Rascunho → Pendente → Aprovado → Baixado.',
                ],
            ]],
            [['admin.tesouraria.mensalidades.'], [
                'titulo' => 'Mensalidades',
                'resumo' => 'Gera automaticamente os lançamentos de receita referentes à mensalidade de cada Irmão ativo em um determinado mês.',
                'itens' => [
                    'Escolha o mês/ano de referência e a categoria e conta que devem ser usadas nos lançamentos gerados.',
                    'O sistema cria um lançamento de receita, em rascunho, para cada Irmão com situação cadastral ativa.',
                    'Depois de gerados, cada lançamento segue o fluxo normal em "Lançamentos": aprove e baixe conforme o pagamento de cada Irmão for confirmado.',
                ],
                'exemplos' => [
                    'Gerar as mensalidades de agosto/2026 cria um lançamento de R$ 80,00 (por exemplo) para cada Irmão ativo naquele mês.',
                ],
            ]],
            [['admin.tesouraria.fechamentos.'], [
                'titulo' => 'Fechamento Financeiro',
                'resumo' => 'Registra o encerramento contábil de um mês, consolidando os valores movimentados no período.',
                'itens' => [
                    'Antes de fechar o mês, confira se todos os lançamentos do período já estão Aprovados e Baixados.',
                    'Informe o ano e o mês de referência e, se quiser, uma observação sobre o fechamento.',
                    'Um mês já fechado fica registrado no histórico da tela, para consulta e auditoria futura.',
                ],
                'exemplos' => [
                    'Fechar "agosto/2026" depois de conferir que a mensalidade do mês e as despesas já foram baixadas.',
                ],
            ]],
            [['admin.tesouraria.'], [
                'titulo' => 'Tesouraria — como funciona',
                'resumo' => 'Este é o módulo financeiro da Loja: controla o dinheiro que entra (receitas), o que sai (despesas), as mensalidades dos Irmãos e o fechamento contábil de cada mês. Siga os passos abaixo, nesta ordem, para manter a Tesouraria organizada.',
                'itens' => [
                    'Cadastre as categorias financeiras em "Categorias" — os tipos de receita e despesa que a Loja movimenta.',
                    'Cadastre as contas em "Contas" — onde o dinheiro fica guardado (caixa físico, conta bancária).',
                    'Registre cada entrada ou saída em "Lançamentos", vinculando sempre uma categoria e uma conta.',
                    'No início do mês, gere as mensalidades dos Irmãos em "Mensalidades".',
                    'Aprove os lançamentos pendentes e marque como "Baixado" assim que o dinheiro realmente entrar ou sair da conta.',
                    'No fim do mês, registre o fechamento em "Fechamentos" para consolidar e travar o período.',
                ],
                'exemplos' => [
                    'Receita: mensalidade de agosto de um Irmão — R$ 80,00, categoria "Mensalidades", conta "Banco do Brasil".',
                    'Despesa: conta de luz do templo — R$ 250,00, categoria "Água e Luz", conta "Caixa da Loja".',
                    'Os cartões de Receitas, Despesas e Saldo no topo desta tela e no Painel só consideram lançamentos já Baixados.',
                ],
            ]],
        ];
    }

    /**
     * @return array{titulo: string, itens: array<int, string>}
     */
    private static function generico(): array
    {
        return [
            'titulo' => 'Como usar o painel',
            'itens' => [
                'Use o menu lateral para navegar entre os módulos do sistema.',
                'As telas de listagem têm botões de ação (editar, ativar, bloquear, remover etc.) na coluna "Ações" de cada linha.',
                'Ações destrutivas ou irreversíveis sempre pedem confirmação antes de serem executadas.',
                'Só aparecem no menu e nesta ajuda os módulos para os quais o seu perfil tem permissão de acesso.',
            ],
        ];
    }
}
