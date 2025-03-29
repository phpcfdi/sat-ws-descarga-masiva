<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\Shared;

use PhpCfdi\SatWsDescargaMasiva\Shared\ComplementoInterface;
use PhpCfdi\SatWsDescargaMasiva\Shared\ComplementoRetenciones;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

final class ComplementoRetencionesTest extends TestCase
{
    public function testCreateUndefinedByName(): void
    {
        $complemento = ComplementoRetenciones::undefined();
        $this->assertInstanceOf(ComplementoInterface::class, $complemento);
        $this->assertTrue($complemento->isUndefined());
    }

    public function testCreateUndefinedByMethod(): void
    {
        $complemento = new ComplementoRetenciones('');
        $this->assertTrue($complemento->isUndefined());
    }

    public function testSample(): void
    {
        $complemento = ComplementoRetenciones::planesRetiro11();
        $this->assertFalse($complemento->isUndefined());
        $this->assertTrue($complemento->isPlanesRetiro11());
        $this->assertSame('planesderetiro11', $complemento->value());
        $this->assertSame('Planes de retiro 1.1', $complemento->label());
        $this->assertEquals(new ComplementoRetenciones('planesderetiro11'), $complemento);
        $this->assertEquals(ComplementoRetenciones::create('planesderetiro11'), $complemento);
    }
}
