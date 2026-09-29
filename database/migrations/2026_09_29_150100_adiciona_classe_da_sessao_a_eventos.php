<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Classe da sessão (ordinária, magna, administrativa, especial) capturada
     * já no lançamento histórico, em vez de ficar só no título livre — o que
     * obrigaria a normalizar dezenas de variações de texto depois.
     *
     * Campo intencionalmente simples e nullable: só se aplica a eventos com
     * tipo=sessao. Quando a entidade Sessao existir (CH-01), esta coluna
     * migra para sessoes.classe e é removida daqui.
     */
    public function up(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            $table->string('sessao_classe', 30)->nullable()->after('tipo');
        });
    }

    public function down(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            $table->dropColumn('sessao_classe');
        });
    }
};
