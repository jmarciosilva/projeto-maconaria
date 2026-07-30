<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('secretaria_numeradores', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 40);
            $table->unsignedSmallInteger('ano');
            $table->unsignedInteger('proximo_numero')->default(1);
            $table->timestamps();

            $table->unique(['tipo', 'ano']);
        });

        Schema::create('secretaria_documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('autor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('aprovado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('publicado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('tipo', 40);
            $table->unsignedSmallInteger('ano');
            $table->unsignedInteger('numero');
            $table->string('codigo')->unique();
            $table->string('titulo');
            $table->longText('conteudo')->nullable();
            $table->string('status', 40)->default('rascunho');
            $table->date('data_documento')->nullable();
            $table->timestamp('aprovado_em')->nullable();
            $table->timestamp('publicado_em')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tipo', 'ano', 'numero']);
            $table->index(['tipo', 'status', 'ano']);
        });

        Schema::create('secretaria_documento_versoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('documento_id')->constrained('secretaria_documentos')->cascadeOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('versao');
            $table->string('titulo');
            $table->longText('conteudo')->nullable();
            $table->string('status', 40);
            $table->timestamps();

            $table->unique(['documento_id', 'versao']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('secretaria_documento_versoes');
        Schema::dropIfExists('secretaria_documentos');
        Schema::dropIfExists('secretaria_numeradores');
    }
};
