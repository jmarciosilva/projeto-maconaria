<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chancelaria_frequencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained('eventos')->cascadeOnDelete();
            $table->foreignId('irmao_id')->constrained('irmaos')->cascadeOnDelete();
            $table->foreignId('registrado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30);
            $table->text('observacao')->nullable();
            $table->timestamps();

            $table->unique(['evento_id', 'irmao_id']);
            $table->index(['status', 'created_at']);
        });

        Schema::create('chancelaria_visitantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->nullable()->constrained('eventos')->nullOnDelete();
            $table->foreignId('registrado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nome');
            $table->string('loja_origem')->nullable();
            $table->string('potencia')->nullable();
            $table->string('documento')->nullable();
            $table->text('observacao')->nullable();
            $table->timestamps();
        });

        Schema::create('chancelaria_comunicados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('autor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('titulo');
            $table->longText('conteudo')->nullable();
            $table->string('status', 30)->default('rascunho');
            $table->timestamp('publicado_em')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chancelaria_comunicados');
        Schema::dropIfExists('chancelaria_visitantes');
        Schema::dropIfExists('chancelaria_frequencias');
    }
};
