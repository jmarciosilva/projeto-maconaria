<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Conclusão do lançamento de frequência de uma sessão.
     *
     * Até aqui o sistema não sabia se o Chanceler havia terminado de lançar
     * uma sessão: a interface inferia isso de "existe pelo menos um
     * registro", o que dá o mesmo resultado para uma sessão com 1 de 40
     * Irmãos lançados e para uma sessão completa.
     *
     * Ambas as colunas são nullable e aditivas: toda sessão existente nasce
     * pendente, sem backfill e sem alterar nenhum registro. Sessões
     * históricas permanecem pendentes até revisão humana.
     *
     * Não usa eventos.status, que trata de publicação (rascunho/publicado) e
     * vale para eventos públicos também.
     *
     * Vive em eventos porque sessão ainda é um Evento com tipo=sessao —
     * mesmo motivo de sessao_classe. Quando a entidade Sessao existir, as
     * duas colunas migram junto.
     */
    public function up(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            $table->timestamp('frequencia_concluida_em')->nullable()->after('sessao_classe');
            $table->foreignId('frequencia_concluida_por_id')
                ->nullable()
                ->after('frequencia_concluida_em')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('frequencia_concluida_por_id');
            $table->dropColumn('frequencia_concluida_em');
        });
    }
};
