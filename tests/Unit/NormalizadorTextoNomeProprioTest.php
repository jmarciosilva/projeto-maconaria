<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\NormalizadorTexto;
use PHPUnit\Framework\TestCase;

final class NormalizadorTextoNomeProprioTest extends TestCase
{
    public function test_normaliza_nome_todo_em_maiusculas(): void
    {
        $this->assertSame('Luis Carlos Esteves', NormalizadorTexto::nomeProprio('LUIS CARLOS ESTEVES'));
    }

    public function test_normaliza_nome_todo_em_minusculas(): void
    {
        $this->assertSame('Luis Carlos Esteves', NormalizadorTexto::nomeProprio('luis carlos esteves'));
    }

    public function test_normaliza_capitalizacao_inconsistente(): void
    {
        $this->assertSame('Luis Carlos Esteves', NormalizadorTexto::nomeProprio('LuIs CaRlOs EsTeVeS'));
    }

    public function test_preserva_acentuacao(): void
    {
        $this->assertSame(
            'José Márcio Ferreira da Silva',
            NormalizadorTexto::nomeProprio('JOSÉ MÁRCIO FERREIRA DA SILVA'),
        );
        $this->assertSame('Gonçalves', NormalizadorTexto::nomeProprio('GONÇALVES'));
        $this->assertSame('Ávila', NormalizadorTexto::nomeProprio('ÁVILA'));
    }

    public function test_mantem_particulas_em_minusculas_quando_nao_iniciam_o_nome(): void
    {
        $this->assertSame('João Carlos dos Santos', NormalizadorTexto::nomeProprio('joão carlos dos santos'));
        $this->assertSame('Maria de Fátima', NormalizadorTexto::nomeProprio('MARIA DE FÁTIMA'));
        $this->assertSame('Pedro das Neves', NormalizadorTexto::nomeProprio('PEDRO DAS NEVES'));
        $this->assertSame('Ana do Carmo', NormalizadorTexto::nomeProprio('ANA DO CARMO'));
        $this->assertSame('Tomás e Silva', NormalizadorTexto::nomeProprio('TOMÁS E SILVA'));
    }

    public function test_capitaliza_particula_quando_e_a_primeira_palavra(): void
    {
        $this->assertSame('Da Silva', NormalizadorTexto::nomeProprio('DA SILVA'));
    }

    public function test_capitaliza_depois_de_hifen(): void
    {
        $this->assertSame('Ana-Maria dos Santos', NormalizadorTexto::nomeProprio('ANA-MARIA DOS SANTOS'));
        $this->assertSame('Jean-Pierre', NormalizadorTexto::nomeProprio('jean-pierre'));
    }

    public function test_capitaliza_depois_de_apostrofo(): void
    {
        $this->assertSame("D'Ávila", NormalizadorTexto::nomeProprio("D'ÁVILA"));
        $this->assertSame("D'Ávila", NormalizadorTexto::nomeProprio("d'ávila"));
    }

    public function test_remove_espacos_das_pontas_e_colapsa_espacos_internos(): void
    {
        $this->assertSame(
            'Luis Carlos Esteves',
            NormalizadorTexto::nomeProprio('  LUIS   CARLOS   ESTEVES  '),
        );
    }

    public function test_trata_nulo_e_string_vazia(): void
    {
        $this->assertNull(NormalizadorTexto::nomeProprio(null));
        $this->assertSame('', NormalizadorTexto::nomeProprio(''));
        $this->assertSame('', NormalizadorTexto::nomeProprio('   '));
    }

    public function test_e_idempotente(): void
    {
        $normalizado = NormalizadorTexto::nomeProprio('JOSÉ MÁRCIO FERREIRA DA SILVA');

        $this->assertSame($normalizado, NormalizadorTexto::nomeProprio($normalizado));
    }

    /**
     * Limitação aceita conscientemente: sem dicionário de exceções não há
     * como preservar intercaps.
     */
    public function test_nao_preserva_intercaps(): void
    {
        $this->assertSame('Mcdonald', NormalizadorTexto::nomeProprio('McDonald'));
    }
}
