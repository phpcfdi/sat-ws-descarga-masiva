<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\Shared;

use PhpCfdi\SatWsDescargaMasiva\Shared\CfdiComplemento;
use PhpCfdi\SatWsDescargaMasiva\Shared\FilterComplement;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

final class CfdiComplementoTest extends TestCase
{
    public function testCreateUndefinedByName(): void
    {
        $complemento = CfdiComplemento::undefined();
        $this->assertInstanceOf(FilterComplement::class, $complemento);
        $this->assertTrue($complemento->isUndefined());
    }

    public function testCreateUndefinedByMethod(): void
    {
        $complemento = new CfdiComplemento('');
        $this->assertTrue($complemento->isUndefined());
    }

    public function testSample(): void
    {
        $complemento = CfdiComplemento::valesDespensa10();
        $this->assertFalse($complemento->isUndefined());
        $this->assertSame('valesdedespensa', $complemento->value());
        $this->assertSame('Vales de despensa 1.0', $complemento->label());
        $this->assertEquals(new CfdiComplemento('valesdedespensa'), $complemento);
    }
}
