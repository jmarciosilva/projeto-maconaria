<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('noticia_fotos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('noticia_id')->constrained('noticias')->cascadeOnDelete();
            $table->string('caminho'); // path to the image file
            $table->string('descricao')->nullable(); // alt text / description
            $table->integer('ordem')->default(0); // for ordering photos
            $table->timestamps();

            $table->index(['noticia_id', 'ordem']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('noticia_fotos');
    }
};
