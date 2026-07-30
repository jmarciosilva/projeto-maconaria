<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('secretaria_documento_arquivos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('documento_id')->constrained('secretaria_documentos')->cascadeOnDelete();
            $table->foreignId('enviado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nome_original');
            $table->string('caminho');
            $table->string('mime', 120);
            $table->unsignedBigInteger('tamanho');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('secretaria_documento_arquivos');
    }
};
