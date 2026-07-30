<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Imagem de capa opcional para notícias, eventos e publicações do mural
     * — exibida em destaque na página inicial. Galeria não recebe este
     * campo: o álbum já usa a primeira fotografia (por `ordem`) como capa.
     */
    public function up(): void
    {
        Schema::table('noticias', function (Blueprint $table) {
            $table->string('imagem_capa')->nullable()->after('conteudo');
        });

        // Snapshot de versão acompanha a capa vigente no momento da versão,
        // mesmo critério já usado para título/conteúdo/status.
        Schema::table('noticia_versoes', function (Blueprint $table) {
            $table->string('imagem_capa')->nullable()->after('conteudo');
        });

        Schema::table('eventos', function (Blueprint $table) {
            $table->string('imagem_capa')->nullable()->after('descricao');
        });

        Schema::table('mural_publicacoes', function (Blueprint $table) {
            $table->string('imagem_capa')->nullable()->after('conteudo');
        });
    }

    public function down(): void
    {
        Schema::table('noticias', function (Blueprint $table) {
            $table->dropColumn('imagem_capa');
        });

        Schema::table('noticia_versoes', function (Blueprint $table) {
            $table->dropColumn('imagem_capa');
        });

        Schema::table('eventos', function (Blueprint $table) {
            $table->dropColumn('imagem_capa');
        });

        Schema::table('mural_publicacoes', function (Blueprint $table) {
            $table->dropColumn('imagem_capa');
        });
    }
};
