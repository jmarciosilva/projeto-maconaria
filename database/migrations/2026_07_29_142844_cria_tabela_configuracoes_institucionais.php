<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabela de configuração única (singleton): existe sempre um único
     * registro (id = 1), acessado via ConfiguracaoInstitucional::atual().
     */
    public function up(): void
    {
        Schema::create('configuracoes_institucionais', function (Blueprint $table) {
            $table->id();
            $table->string('logotipo')->nullable();
            $table->text('endereco_rodape')->nullable();
            $table->string('email_institucional')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('tiktok_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracoes_institucionais');
    }
};
