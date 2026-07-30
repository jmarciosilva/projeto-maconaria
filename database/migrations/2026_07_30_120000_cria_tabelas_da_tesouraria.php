<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tesouraria_categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('tipo', 20);
            $table->boolean('ativa')->default(true);
            $table->timestamps();
        });

        Schema::create('tesouraria_contas', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('instituicao')->nullable();
            $table->string('tipo', 30)->default('caixa');
            $table->bigInteger('saldo_inicial_centavos')->default(0);
            $table->boolean('ativa')->default(true);
            $table->timestamps();
        });

        Schema::create('tesouraria_lancamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_id')->constrained('tesouraria_categorias')->restrictOnDelete();
            $table->foreignId('conta_id')->constrained('tesouraria_contas')->restrictOnDelete();
            $table->foreignId('irmao_id')->nullable()->constrained('irmaos')->nullOnDelete();
            $table->foreignId('criado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('aprovado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('tipo', 20);
            $table->string('status', 30)->default('rascunho');
            $table->string('descricao');
            $table->bigInteger('valor_centavos');
            $table->date('data_competencia');
            $table->date('data_vencimento')->nullable();
            $table->date('data_pagamento')->nullable();
            $table->text('observacao')->nullable();
            $table->timestamp('aprovado_em')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tipo', 'status', 'data_competencia']);
        });

        Schema::create('tesouraria_fechamentos', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('ano');
            $table->unsignedTinyInteger('mes');
            $table->foreignId('fechado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fechado_em');
            $table->text('observacao')->nullable();
            $table->timestamps();

            $table->unique(['ano', 'mes']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tesouraria_fechamentos');
        Schema::dropIfExists('tesouraria_lancamentos');
        Schema::dropIfExists('tesouraria_contas');
        Schema::dropIfExists('tesouraria_categorias');
    }
};
