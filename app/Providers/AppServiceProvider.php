<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\UsuarioPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // O nome da policy (UsuarioPolicy) não segue a convenção automática de
        // descoberta do Laravel para o model User, por isso o registro é manual.
        Gate::policy(User::class, UsuarioPolicy::class);

        // O perfil de Superadministrador sempre tem acesso total, independente
        // das permissões atribuídas a ele, para que nunca fique acidentalmente
        // sem acesso caso uma permissão seja removida do perfil.
        Gate::before(function (User $user, string $ability) {
            return $user->hasRole('Superadministrador') ? true : null;
        });
    }
}
