<?php

declare(strict_types=1);

namespace App\Support\Ajuda;

/**
 * Texto de ajuda contextual mostrado no modal acionado pelo botão "Ajuda"
 * do painel administrativo. O conteúdo é escolhido pelo nome da rota atual
 * (prefixo) — quando nenhuma entrada específica bate, cai no bloco genérico.
 */
final class ConteudoAjuda
{
    /**
     * @return array{titulo: string, itens: array<int, string>}
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
     * @return array<int, array{0: array<int, string>, 1: array{titulo: string, itens: array<int, string>}}>
     */
    private static function mapa(): array
    {
        return [
            [['admin.dashboard'], [
                'titulo' => 'Painel',
                'itens' => [
                    'Esta tela reúne os principais números do sistema: usuários, tesouraria, chancelaria e conteúdo do site.',
                    'Os grupos de cartões exibidos dependem das permissões do seu perfil — só aparece o que você tem acesso a visualizar.',
                    'Os números de tesouraria consideram apenas lançamentos já baixados; os de chancelaria consideram os últimos 3 meses.',
                    'Use os links "Ver tesouraria →" e "Ver chancelaria →" em cada bloco para abrir o módulo completo.',
                ],
            ]],
            [['admin.usuarios.'], [
                'titulo' => 'Usuários',
                'itens' => [
                    'Lista as contas com acesso ao sistema: nome, e-mail, status, perfis e último acesso.',
                    '"Novo usuário" cria uma conta e define os perfis de acesso dela.',
                    '"Editar" altera dados cadastrais e perfis. "Ativar/Desativar" controla se o usuário consegue fazer login. "Bloquear/Desbloquear" é usado em caso de suspeita de uso indevido da conta.',
                    'Por segurança, você não pode desativar nem bloquear a própria conta.',
                ],
            ]],
            [['admin.tesouraria.'], [
                'titulo' => 'Tesouraria',
                'itens' => [
                    'Painel financeiro da Loja: contas, categorias, lançamentos (receitas e despesas), mensalidades e fechamentos mensais.',
                    'Um lançamento segue o fluxo rascunho → pendente → aprovado → baixado. Use "Aprovar" e "Baixar" para avançar esse fluxo.',
                    '"Fechamentos" registra o fechamento contábil de cada mês, consolidando o período.',
                    '"Mensalidades" gera lançamentos de mensalidade para os Irmãos de forma recorrente.',
                ],
            ]],
            [['admin.chancelaria.'], [
                'titulo' => 'Chancelaria',
                'itens' => [
                    'Controla frequência às sessões, visitantes recebidos e comunicados formais da Loja.',
                    '"Frequências" registra presença, ausência ou justificativa de cada Irmão em uma sessão específica.',
                    '"Visitantes" cadastra Irmãos de outras Lojas que compareceram a uma sessão.',
                    '"Comunicados" registra avisos formais emitidos pela Chancelaria.',
                ],
            ]],
            [['admin.noticias.', 'admin.noticia-categorias.', 'admin.noticia-tags.'], [
                'titulo' => 'Notícias',
                'itens' => [
                    'Gerencia as notícias publicadas no site público, além das categorias e tags usadas para organizá-las.',
                    'Uma notícia só aparece no site quando está com status "Publicada" e visibilidade pública.',
                    '"Categorias" e "Tags" ajudam os visitantes a filtrar e encontrar notícias relacionadas.',
                ],
            ]],
            [['admin.eventos.'], [
                'titulo' => 'Eventos',
                'itens' => [
                    'Cadastra sessões e eventos da Loja, exibidos publicamente em "Eventos" e no "Calendário" do site.',
                    '"Calendário" mostra a visão mensal de todos os eventos cadastrados.',
                    'Eventos com visibilidade pública aparecem no site institucional; os demais ficam restritos à Área Restrita.',
                ],
            ]],
            [['admin.mural.'], [
                'titulo' => 'Mural',
                'itens' => [
                    'Gerencia as publicações do mural social, exibido publicamente no site.',
                    'Comentários enviados por usuários passam por aprovação antes de aparecerem publicamente — use "Aprovar" para liberá-los.',
                    'Curtidas (reações) são registradas automaticamente pelos usuários autenticados e não precisam de moderação.',
                ],
            ]],
            [['admin.galeria.'], [
                'titulo' => 'Galeria',
                'itens' => [
                    'Gerencia os álbuns de fotos exibidos na Galeria pública do site.',
                    'Cada álbum reúne várias fotografias; publique o álbum para que ele passe a aparecer no site.',
                ],
            ]],
            [['admin.secretaria.'], [
                'titulo' => 'Secretaria',
                'itens' => [
                    'Gerencia documentos institucionais da secretaria (atas, editais, comunicados administrativos).',
                    'Um documento segue o fluxo rascunho → aprovado → publicado. Use "Aprovar" e "Publicar" para avançar.',
                    'Documentos publicados ficam disponíveis para os Irmãos na Área Restrita.',
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
