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
 *
 * Os perfis são propositalmente poucos e amplos (um por cargo/situação da
 * Loja). Qualquer permissão extra que um usuário específico precise, fora do
 * que o perfil dele já cobre, é concedida diretamente a ele na tela
 * "Permissões individuais" de Usuários (ver UsuarioController), em vez de
 * criar um novo perfil só para esse caso.
 */
final class PerfilPermissaoSeeder extends Seeder
{
    /**
     * @var array<int, string>
     */
    private const PERMISSOES = [
        'usuarios.visualizar', 'usuarios.criar', 'usuarios.editar', 'usuarios.excluir', 'usuarios.atribuir-perfis',
        'perfis.visualizar', 'perfis.criar', 'perfis.editar', 'perfis.excluir',
        'recados.visualizar', 'recados.criar', 'recados.editar',
        'irmaos.visualizar', 'irmaos.criar', 'irmaos.editar', 'irmaos.excluir',
        'cms.visualizar', 'cms.editar',
        'noticias.visualizar', 'noticias.criar', 'noticias.editar', 'noticias.publicar', 'noticias.excluir',
        'eventos.visualizar', 'eventos.criar', 'eventos.editar', 'eventos.excluir',
        'tesouraria.visualizar', 'tesouraria.criar', 'tesouraria.editar', 'tesouraria.aprovar', 'tesouraria.excluir',
        'secretaria.visualizar', 'secretaria.criar-ata', 'secretaria.editar-ata', 'secretaria.aprovar-ata', 'secretaria.publicar-ata',
        'chancelaria.visualizar', 'chancelaria.criar', 'chancelaria.editar',
        'documentos.visualizar', 'documentos.enviar', 'documentos.avaliar', 'documentos.excluir',
        'galeria.visualizar', 'galeria.criar', 'galeria.editar', 'galeria.excluir',
        'mural.visualizar', 'mural.criar', 'mural.editar', 'mural.moderar', 'mural.excluir',
        'configuracoes.visualizar', 'configuracoes.editar',
        'auditoria.visualizar',
    ];

    /**
     * @var array<string, array<int, string>>
     */
    private const PERFIS = [
        'Administrador' => self::PERMISSOES, // Também recebe acesso total via Gate::before (ver AppServiceProvider).
        'Secretário' => [
            'irmaos.visualizar', 'irmaos.criar', 'irmaos.editar', 'secretaria.visualizar', 'secretaria.criar-ata',
            'secretaria.editar-ata', 'secretaria.publicar-ata', 'eventos.visualizar', 'eventos.criar', 'eventos.editar',
            'recados.visualizar', 'recados.criar', 'recados.editar',
        ],
        'Tesoureiro' => [
            'tesouraria.visualizar', 'tesouraria.criar', 'tesouraria.editar', 'tesouraria.excluir', 'irmaos.visualizar',
            'recados.visualizar', 'recados.criar', 'recados.editar',
        ],
        'Chanceler' => [
            'chancelaria.visualizar', 'chancelaria.criar', 'chancelaria.editar', 'irmaos.visualizar',
            'galeria.visualizar', 'galeria.criar', 'mural.visualizar', 'mural.criar', 'mural.moderar',
            'recados.visualizar', 'recados.criar', 'recados.editar',
        ],
        'Irmão' => [],
        'Visitante Autorizado' => [],
    ];

    /**
     * Perfis existentes em bancos antigos que deixaram de fazer sentido como
     * perfil próprio (Fase de redução dos perfis a um conjunto fixo e
     * amplo). Qualquer usuário que ainda tenha um desses perfis recebe as
     * mesmas permissões diretamente (perfil de acesso individual), para não
     * perder acesso, e o perfil antigo é removido.
     *
     * @var array<string, array<int, string>>
     */
    private const PERFIS_DESCONTINUADOS = [
        'Venerável Mestre' => [
            'usuarios.visualizar', 'irmaos.visualizar', 'cms.visualizar', 'noticias.visualizar',
            'eventos.visualizar', 'tesouraria.visualizar', 'tesouraria.aprovar',
            'secretaria.visualizar', 'secretaria.aprovar-ata', 'chancelaria.visualizar',
            'documentos.visualizar', 'galeria.visualizar', 'mural.visualizar', 'mural.moderar', 'auditoria.visualizar',
            'recados.visualizar', 'recados.criar', 'recados.editar',
        ],
        'Bibliotecário' => [
            'documentos.visualizar', 'documentos.enviar', 'documentos.avaliar',
        ],
        'Editor de Conteúdo' => [
            'cms.visualizar', 'cms.editar', 'noticias.visualizar', 'noticias.criar', 'noticias.editar',
            'galeria.visualizar', 'galeria.criar', 'galeria.editar', 'mural.visualizar', 'mural.criar', 'mural.editar',
        ],
        'Instrutor' => [
            'documentos.visualizar', 'documentos.avaliar',
        ],
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

        $this->migrarSuperadministrador();
        $this->migrarPerfisDescontinuados();
    }

    /**
     * O perfil Superadministrador foi removido: o bypass de acesso total
     * (Gate::before, ver AppServiceProvider) passou a valer para o perfil
     * Administrador. Quem ainda tiver Superadministrador vira Administrador.
     */
    private function migrarSuperadministrador(): void
    {
        $role = Role::where('name', 'Superadministrador')->where('guard_name', 'web')->first();

        if (! $role) {
            return;
        }

        foreach ($role->users as $usuario) {
            $usuario->assignRole('Administrador');
            $usuario->removeRole($role);
        }

        $role->delete();
    }

    private function migrarPerfisDescontinuados(): void
    {
        foreach (self::PERFIS_DESCONTINUADOS as $perfil => $permissoes) {
            $role = Role::where('name', $perfil)->where('guard_name', 'web')->first();

            if (! $role) {
                continue;
            }

            foreach ($role->users as $usuario) {
                $usuario->givePermissionTo($permissoes);
                $usuario->removeRole($role);
            }

            $role->delete();
        }
    }
}
