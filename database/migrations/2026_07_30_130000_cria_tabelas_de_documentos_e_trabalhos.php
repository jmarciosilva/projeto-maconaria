<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documento_atividades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('autor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->string('status', 30)->default('rascunho');
            $table->timestamp('publicado_em')->nullable();
            $table->timestamp('prazo_entrega_em')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'prazo_entrega_em']);
        });

        Schema::create('documento_entregas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('atividade_id')->constrained('documento_atividades')->cascadeOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->string('status', 30)->default('enviada');
            $table->timestamp('enviado_em');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['atividade_id', 'usuario_id']);
        });

        Schema::create('documento_avaliacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entrega_id')->constrained('documento_entregas')->cascadeOnDelete();
            $table->foreignId('avaliador_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('nota')->nullable();
            $table->text('parecer')->nullable();
            $table->timestamp('avaliado_em');
            $table->timestamps();
        });

        Schema::create('documento_comentarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('atividade_id')->constrained('documento_atividades')->cascadeOnDelete();
            $table->foreignId('entrega_id')->nullable()->constrained('documento_entregas')->cascadeOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('comentario');
            $table->timestamps();
        });

        Schema::create('documento_arquivos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('atividade_id')->nullable()->constrained('documento_atividades')->cascadeOnDelete();
            $table->foreignId('entrega_id')->nullable()->constrained('documento_entregas')->cascadeOnDelete();
            $table->foreignId('enviado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nome_original');
            $table->string('caminho');
            $table->string('mime');
            $table->unsignedBigInteger('tamanho');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documento_arquivos');
        Schema::dropIfExists('documento_comentarios');
        Schema::dropIfExists('documento_avaliacoes');
        Schema::dropIfExists('documento_entregas');
        Schema::dropIfExists('documento_atividades');
    }
};
