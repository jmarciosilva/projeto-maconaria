<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('telefone')->nullable()->after('email');
            $table->string('status')->default('ativo')->after('password');
            $table->boolean('deve_alterar_senha')->default(false)->after('status');
            $table->timestamp('bloqueado_em')->nullable()->after('deve_alterar_senha');
            $table->timestamp('ultimo_acesso_em')->nullable()->after('bloqueado_em');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'telefone',
                'status',
                'deve_alterar_senha',
                'bloqueado_em',
                'ultimo_acesso_em',
            ]);
        });
    }
};
