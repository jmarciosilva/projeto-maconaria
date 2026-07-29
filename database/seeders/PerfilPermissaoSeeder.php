<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Semeia o catálogo de permissões e os perfis iniciais descritos no escopo do
 * projeto. O mapeamento perfil -> permissões não foi totalmente detalhado no
 * escopo original; a distribuição abaixo é uma suposição conservadora
 * documentada em docs/MODULOS.md e poderá ser ajustada por um administrador
 * futuramente, já que os perfis são configuráveis.
 */
final class PerfilPermissaoSeeder extends Seeder
{
    /**
     * @var array<int, string>
     */
    private const PERMISSOES = [
        'usuarios.visualizar', 'usuarios.criar', 'usuarios.editar', 'usuarios.excluir', 'usuarios.atribuir-perfis',
        'perfis.visualizar', 'perfis.criar', 'perfis.editar', 'perfis.excluir',
        'irmaos.visualizar', 'irmaos.criar', 'irmaos.editar', 'irmaos.excluir',
        'cms.visualizar', 'cms.editar',
        'noticias.visualizar', 'noticias.criar', 'noticias.editar', 'noticias.publicar', 'noticias.excluir',
        'eventos.visualizar', 'eventos.criar', 'eventos.editar', 'eventos.excluir',
        'tesouraria.visualizar', 'tesouraria.criar', 'tesouraria.editar', 'tesouraria.aprovar', 'tesouraria.excluir',
        'secretaria.visualizar', 'secretaria.criar-ata', 'secretaria.editar-ata', 'secretaria.aprovar-ata', 'secretaria.publicar-ata',
        'chancelaria.visualizar', 'chancelaria.criar', 'chancelaria.editar',
        'documentos.visualizar', 'documentos.enviar', 'documentos.avaliar',
        'configuracoes.visualizar', 'configuracoes.editar',
        'auditoria.visualizar',
    ];

    /**
     * @var array<string, array<int, string>>
     */
    private const PERFIS = [
        'Superadministrador' => [], // Recebe acesso total via Gate::before (ver AppServiceProvider).
        'Administrador' => self::PERMISSOES,
        'Venerável Mestre' => [
            'usuarios.visualizar', 'irmaos.visualizar', 'cms.visualizar', 'noticias.visualizar',
            'eventos.visualizar', 'tesouraria.visualizar', 'tesouraria.aprovar',
            'secretaria.visualizar', 'secretaria.aprovar-ata', 'chancelaria.visualizar',
            'documentos.visualizar', 'auditoria.visualizar',
        ],
        'Secretário' => [
            'irmaos.visualizar', 'secretaria.visualizar', 'secretaria.criar-ata',
            'secretaria.editar-ata', 'secretaria.publicar-ata', 'eventos.visualizar', 'eventos.criar', 'eventos.editar',
        ],
        'Tesoureiro' => [
            'tesouraria.visualizar', 'tesouraria.criar', 'tesouraria.editar', 'tesouraria.excluir', 'irmaos.visualizar',
        ],
        'Chanceler' => [
            'chancelaria.visualizar', 'chancelaria.criar', 'chancelaria.editar', 'irmaos.visualizar',
        ],
        'Bibliotecário' => [
            'documentos.visualizar', 'documentos.enviar', 'documentos.avaliar',
        ],
        'Editor de Conteúdo' => [
            'cms.visualizar', 'cms.editar', 'noticias.visualizar', 'noticias.criar', 'noticias.editar', 'noticias.publicar',
        ],
        'Instrutor' => [
            'documentos.visualizar', 'documentos.avaliar',
        ],
        'Irmão' => [],
        'Visitante Autorizado' => [],
    ];

    public function run(): void
    {
        foreach (self::PERMISSOES as $permissao) {
            Permission::findOrCreate($permissao, 'web');
        }

        foreach (self::PERFIS as $perfil => $permissoes) {
            $role = Role::findOrCreate($perfil, 'web');
            $role->syncPermissions($permissoes);
        }
    }
}
