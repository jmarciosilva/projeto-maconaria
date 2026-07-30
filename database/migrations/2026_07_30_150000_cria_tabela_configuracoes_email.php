<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabela de configuração única (singleton): existe sempre um único
     * registro, acessado via ConfiguracaoEmail::atual(), seguindo o mesmo
     * padrão de ConfiguracaoInstitucional.
     */
    public function up(): void
    {
        Schema::create('configuracoes_email', function (Blueprint $table) {
            $table->id();
            $table->string('mailer', 20)->default('log');
            $table->string('host')->nullable();
            $table->unsignedInteger('porta')->nullable();
            $table->string('usuario')->nullable();
            // Armazenada com o cast "encrypted" do Eloquent (Crypt/APP_KEY) —
            // nunca em texto puro, mesmo com acesso direto ao banco.
            $table->text('senha')->nullable();
            $table->string('criptografia', 10)->nullable();
            $table->string('remetente_nome')->nullable();
            $table->string('remetente_email')->nullable();
            $table->boolean('ativo')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracoes_email');
    }
};
