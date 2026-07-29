<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\StatusUsuario;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Cria (ou atualiza) o administrador local usado em ambiente de
 * desenvolvimento. Nunca deve ser executado em produção, pois depende de
 * uma senha padrão definida no .env.
 */
final class AdministradorLocalSeeder extends Seeder
{
    public function run(): void
    {
        if (App::environment('production')) {
            Log::warning('AdministradorLocalSeeder ignorado: não é permitido executar em produção.');

            return;
        }

        $nome = (string) config('app.admin_seed.name', 'Administrador Local');
        $email = (string) config('app.admin_seed.email', 'admin@localhost.test');
        $senha = (string) config('app.admin_seed.password', 'alterar-senha');

        $usuario = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $nome,
                'password' => Hash::make($senha),
                'status' => StatusUsuario::ATIVO,
                'email_verified_at' => now(),
            ]
        );

        $usuario->syncRoles(['Superadministrador']);

        $this->command?->info("Administrador local pronto: {$email}");
    }
}
