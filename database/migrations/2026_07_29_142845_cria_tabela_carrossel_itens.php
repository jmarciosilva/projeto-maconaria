<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carrossel_itens', function (Blueprint $table) {
            $table->id();
            $table->string('titulo')->nullable();
            $table->string('subtitulo')->nullable();
            $table->string('imagem_desktop');
            $table->string('imagem_mobile')->nullable();
            $table->string('texto_alternativo');
            $table->string('link')->nullable();
            $table->string('texto_botao')->nullable();
            $table->boolean('abrir_em_nova_aba')->default(false);
            $table->unsignedInteger('ordem')->default(0);
            $table->date('data_inicio')->nullable();
            $table->date('data_fim')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->index(['ativo', 'ordem']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carrossel_itens');
    }
};
