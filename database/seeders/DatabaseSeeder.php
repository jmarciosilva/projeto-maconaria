<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PerfilPermissaoSeeder::class,
            AdministradorLocalSeeder::class,
            PaginaInstitucionalSeeder::class,
            NoticiaSeeder::class,
            EventoSeeder::class,
            SecretariaSeeder::class,
            ChancelariaSeeder::class,
            TesourariaSeeder::class,
            DocumentoTrabalhoSeeder::class,
            GaleriaMuralSeeder::class,
        ]);
    }
}
