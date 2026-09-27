<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Identificador de login (6 dígitos), no lugar do e-mail. Nullable
            // no banco para não quebrar linhas já existentes; a obrigatoriedade
            // é garantida pela validação do formulário de cadastro.
            $table->string('codigo_cim', 6)->nullable()->unique()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('codigo_cim');
        });
    }
};
