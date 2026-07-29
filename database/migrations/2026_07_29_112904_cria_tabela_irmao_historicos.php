<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabela única para o histórico de cargo, grau, situação cadastral e
     * demais alterações cadastrais relevantes do Irmão, discriminado pelo
     * campo "tipo". Optou-se por uma tabela consolidada em vez de quatro
     * tabelas separadas (ver docs/MODULOS.md).
     */
    public function up(): void
    {
        Schema::create('irmao_historicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('irmao_id')->constrained('irmaos')->cascadeOnDelete();
            $table->string('tipo');
            $table->string('valor_anterior')->nullable();
            $table->string('valor_novo')->nullable();
            $table->date('data_referencia');
            $table->text('observacao')->nullable();
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('criado_em')->useCurrent();

            $table->index(['irmao_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('irmao_historicos');
    }
};
