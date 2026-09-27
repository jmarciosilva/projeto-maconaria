<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('painel_recados', function (Blueprint $table) {
            $table->id();
            $table->string('categoria', 20);
            $table->string('titulo');
            $table->longText('conteudo')->nullable();
            $table->foreignId('autor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('ativo')->default(true);
            $table->timestamp('publicado_em')->nullable();
            $table->date('valido_ate')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['categoria', 'ativo', 'publicado_em']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('painel_recados');
    }
};
