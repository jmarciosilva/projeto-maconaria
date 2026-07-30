<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eventos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('autor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('titulo');
            $table->string('slug')->unique();
            $table->text('descricao')->nullable();
            $table->string('tipo', 30)->default('evento');
            $table->string('status', 30)->default('rascunho');
            $table->string('visibilidade', 30)->default('publica');
            $table->string('local')->nullable();
            $table->timestamp('inicio_em');
            $table->timestamp('fim_em')->nullable();
            $table->timestamp('inscricoes_ate')->nullable();
            $table->unsignedInteger('capacidade')->nullable();
            $table->boolean('permite_confirmacao')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'visibilidade', 'inicio_em']);
            $table->index(['tipo', 'inicio_em']);
        });

        Schema::create('evento_confirmacoes_presenca', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained('eventos')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 30)->default('confirmado');
            $table->text('observacao')->nullable();
            $table->timestamps();

            $table->unique(['evento_id', 'usuario_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evento_confirmacoes_presenca');
        Schema::dropIfExists('eventos');
    }
};
