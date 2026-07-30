<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('galeria_albuns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('autor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('titulo');
            $table->string('slug')->unique();
            $table->text('descricao')->nullable();
            $table->string('status', 30)->default('rascunho');
            $table->string('visibilidade', 30)->default('restrita');
            $table->timestamp('publicado_em')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'visibilidade']);
        });

        Schema::create('galeria_fotografias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('album_id')->constrained('galeria_albuns')->cascadeOnDelete();
            $table->foreignId('enviado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('titulo')->nullable();
            $table->string('texto_alternativo');
            $table->string('caminho');
            $table->string('mime');
            $table->unsignedBigInteger('tamanho');
            $table->unsignedInteger('ordem')->default(0);
            $table->timestamps();
        });

        Schema::create('mural_publicacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('autor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('titulo');
            $table->text('conteudo');
            $table->string('status', 30)->default('rascunho');
            $table->string('visibilidade', 30)->default('restrita');
            $table->timestamp('publicado_em')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'visibilidade', 'publicado_em']);
        });

        Schema::create('mural_comentarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publicacao_id')->constrained('mural_publicacoes')->cascadeOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('comentario');
            $table->boolean('aprovado')->default(false);
            $table->timestamp('aprovado_em')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('mural_reacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publicacao_id')->constrained('mural_publicacoes')->cascadeOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('tipo', 30);
            $table->timestamps();

            $table->unique(['publicacao_id', 'usuario_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mural_reacoes');
        Schema::dropIfExists('mural_comentarios');
        Schema::dropIfExists('mural_publicacoes');
        Schema::dropIfExists('galeria_fotografias');
        Schema::dropIfExists('galeria_albuns');
    }
};
