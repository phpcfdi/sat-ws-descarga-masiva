<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\Shared;

use PhpCfdi\SatWsDescargaMasiva\Shared\FilterComplement;
use PhpCfdi\SatWsDescargaMasiva\Shared\RetencionesComplemento;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

final class RetencionesComplementoTest extends TestCase
{
    public function testCreateUndefinedByName(): void
    {
        $complemento = RetencionesComplemento::undefined();
        $this->assertInstanceOf(FilterComplement::class, $complemento);
        $this->assertTrue($complemento->isUndefined());
    }

    public function testCreateUndefinedByMethod(): void
    {
        $complemento = new RetencionesComplemento('');
        $this->assertTrue($complemento->isUndefined());
    }

    public function testSample(): void
    {
        $complemento = RetencionesComplemento::planesRetiro11();
        $this->assertFalse($complemento->isUndefined());
        $this->assertSame('planesderetiro11', $complemento->value());
        $this->assertSame('Planes de retiro 1.1', $complemento->label());
        $this->assertEquals(new RetencionesComplemento('planesderetiro11'), $complemento);
    }
}
