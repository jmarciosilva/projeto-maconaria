<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('irmaos', function (Blueprint $table) {
            $table->id();

            $table->string('nome_completo');
            $table->string('nome_social')->nullable();
            $table->date('data_nascimento')->nullable();
            $table->string('cpf', 11)->unique();
            $table->string('rg')->nullable();
            $table->string('email')->nullable();
            $table->string('telefone')->nullable();

            $table->string('endereco')->nullable();
            $table->string('numero', 20)->nullable();
            $table->string('complemento')->nullable();
            $table->string('bairro')->nullable();
            $table->string('cidade')->nullable();
            $table->string('estado', 2)->nullable();
            $table->string('cep', 9)->nullable();

            $table->date('data_iniciacao')->nullable();
            $table->date('data_elevacao')->nullable();
            $table->date('data_exaltacao')->nullable();
            $table->string('cim')->nullable();
            $table->string('grau_atual')->nullable();
            $table->string('situacao_cadastral')->default('ativo');
            $table->string('cargo_atual')->nullable();
            $table->date('data_ingresso_loja')->nullable();
            $table->date('data_desligamento')->nullable();

            $table->text('observacoes_administrativas')->nullable();
            $table->string('fotografia')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['situacao_cadastral']);
            $table->index(['grau_atual']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('irmaos');
    }
};
