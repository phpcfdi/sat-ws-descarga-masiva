<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\Shared;

use PhpCfdi\SatWsDescargaMasiva\Shared\ComplementoCfdi;
use PhpCfdi\SatWsDescargaMasiva\Shared\ComplementoInterface;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

final class ComplementoCfdiTest extends TestCase
{
    public function testCreateUndefinedByName(): void
    {
        $complemento = ComplementoCfdi::undefined();
        $this->assertInstanceOf(ComplementoInterface::class, $complemento);
        $this->assertTrue($complemento->isUndefined());
    }

    public function testCreateUndefinedByMethod(): void
    {
        $complemento = new ComplementoCfdi('');
        $this->assertTrue($complemento->isUndefined());
    }

    public function testSample(): void
    {
        $complemento = ComplementoCfdi::valesDespensa10();
        $this->assertFalse($complemento->isUndefined());
        $this->assertSame('valesdedespensa', $complemento->value());
        $this->assertSame('Vales de despensa 1.0', $complemento->label());
        $this->assertEquals(new ComplementoCfdi('valesdedespensa'), $complemento);
    }
}
