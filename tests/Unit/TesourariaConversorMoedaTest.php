<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\Tesouraria\ConversorMoeda;
use PHPUnit\Framework\TestCase;

final class TesourariaConversorMoedaTest extends TestCase
{
    public function test_converte_valor_brasileiro_para_centavos(): void
    {
        $this->assertSame(123456, ConversorMoeda::paraCentavos('1.234,56'));
        $this->assertSame(1000, ConversorMoeda::paraCentavos('10,00'));
    }

    public function test_formata_centavos_com_padrao_brasileiro(): void
    {
        $this->assertSame('1.234,56', ConversorMoeda::formatar(123456));
    }
}
