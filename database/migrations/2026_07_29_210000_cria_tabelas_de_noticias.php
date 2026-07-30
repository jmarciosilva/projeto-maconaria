<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('noticia_categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('slug')->unique();
            $table->text('descricao')->nullable();
            $table->boolean('ativa')->default(true);
            $table->timestamps();
        });

        Schema::create('noticia_tags', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('noticias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_id')->nullable()->constrained('noticia_categorias')->nullOnDelete();
            $table->foreignId('autor_id')->constrained('users')->cascadeOnDelete();
            $table->string('titulo');
            $table->string('slug')->unique();
            $table->text('resumo')->nullable();
            $table->longText('conteudo')->nullable();
            $table->string('status', 30)->default('rascunho');
            $table->string('visibilidade', 30)->default('publica');
            $table->boolean('destaque')->default(false);
            $table->timestamp('publicado_em')->nullable();
            $table->timestamp('agendado_para')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('noticia_tag', function (Blueprint $table) {
            $table->foreignId('noticia_id')->constrained('noticias')->cascadeOnDelete();
            $table->foreignId('noticia_tag_id')->constrained('noticia_tags')->cascadeOnDelete();
            $table->primary(['noticia_id', 'noticia_tag_id']);
        });

        Schema::create('noticia_versoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('noticia_id')->constrained('noticias')->cascadeOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('versao');
            $table->string('titulo');
            $table->text('resumo')->nullable();
            $table->longText('conteudo')->nullable();
            $table->string('status', 30);
            $table->string('visibilidade', 30);
            $table->boolean('destaque')->default(false);
            $table->timestamp('publicado_em')->nullable();
            $table->timestamp('agendado_para')->nullable();
            $table->timestamps();

            $table->unique(['noticia_id', 'versao']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('noticia_versoes');
        Schema::dropIfExists('noticia_tag');
        Schema::dropIfExists('noticias');
        Schema::dropIfExists('noticia_tags');
        Schema::dropIfExists('noticia_categorias');
    }
};
