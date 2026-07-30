<?php

namespace Tests\Unit;

use App\Enums\TipoDocumentoSecretaria;
use App\Support\Secretaria\ProximoNumeroDocumento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecretariaNumeracaoTest extends TestCase
{
    use RefreshDatabase;

    public function test_numbering_is_sequential_by_type_and_year(): void
    {
        $servico = new ProximoNumeroDocumento;

        $primeiro = $servico->reservar(TipoDocumentoSecretaria::ATA, 2026);
        $segundo = $servico->reservar(TipoDocumentoSecretaria::ATA, 2026);
        $outraSerie = $servico->reservar(TipoDocumentoSecretaria::CORRESPONDENCIA, 2026);

        $this->assertSame(1, $primeiro);
        $this->assertSame(2, $segundo);
        $this->assertSame(1, $outraSerie);
    }

    public function test_code_uses_type_year_and_number(): void
    {
        $servico = new ProximoNumeroDocumento;

        $this->assertSame('ATA-2026-0007', $servico->codigo(TipoDocumentoSecretaria::ATA, 2026, 7));
    }
}
