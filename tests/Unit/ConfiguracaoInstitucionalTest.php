<?php

namespace Tests\Unit;

use App\Models\ConfiguracaoInstitucional;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConfiguracaoInstitucionalTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Regressão: atual() já criou um novo registro em branco a cada
     * chamada (bug do firstOrCreate(['id' => 1]) — "id" não é
     * mass-assignable, então o registro nunca ficava com id = 1 de fato).
     */
    public function test_atual_always_returns_the_same_singleton_record(): void
    {
        $primeira = ConfiguracaoInstitucional::atual();
        $primeira->update(['email_institucional' => 'contato@example.com']);

        $segunda = ConfiguracaoInstitucional::atual();
        $terceira = ConfiguracaoInstitucional::atual();

        $this->assertSame($primeira->id, $segunda->id);
        $this->assertSame($primeira->id, $terceira->id);
        $this->assertSame('contato@example.com', $segunda->email_institucional);
        $this->assertSame(1, ConfiguracaoInstitucional::count());
    }
}
