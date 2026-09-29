<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Permite o cadastro inicial do Irmão apenas com nome e CIM, para o
     * lançamento da frequência histórica.
     *
     * - cpf passa a aceitar NULL. O índice único é mantido: MariaDB/MySQL
     *   permitem vários NULL num índice único, então continua impossível
     *   repetir um CPF informado.
     * - cim ganha índice único. Como o CPF deixa de ser obrigatório, o CIM
     *   passa a ser a referência prática contra cadastro duplicado. Continua
     *   nullable, porque nem todo cadastro terá CIM no momento da criação.
     */
    public function up(): void
    {
        Schema::table('irmaos', function (Blueprint $table) {
            $table->string('cpf', 11)->nullable()->change();
            $table->unique('cim');
        });
    }

    public function down(): void
    {
        Schema::table('irmaos', function (Blueprint $table) {
            $table->dropUnique(['cim']);
            $table->string('cpf', 11)->nullable(false)->change();
        });
    }
};
