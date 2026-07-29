<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('configuracoes_institucionais', function (Blueprint $table) {
            $table->string('nome_loja')->nullable()->after('id');
            $table->string('titulo_institucional')->nullable()->after('nome_loja');
            $table->string('subtitulo_institucional')->nullable()->after('titulo_institucional');
            $table->string('telefone_institucional')->nullable()->after('subtitulo_institucional');
        });
    }

    public function down(): void
    {
        Schema::table('configuracoes_institucionais', function (Blueprint $table) {
            $table->dropColumn(['nome_loja', 'titulo_institucional', 'subtitulo_institucional', 'telefone_institucional']);
        });
    }
};
