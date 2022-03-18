<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\Shared;

use PhpCfdi\SatWsDescargaMasiva\Shared\FilterComplement;
use PhpCfdi\SatWsDescargaMasiva\Shared\UndefinedComplemento;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

final class UndefinedComplementoTest extends TestCase
{
    public function testCreateUndefinedByName(): void
    {
        $complemento = UndefinedComplemento::undefined();
        $this->assertInstanceOf(FilterComplement::class, $complemento);
        $this->assertTrue($complemento->isUndefined());
    }

    public function testCreateUndefinedByMethod(): void
    {
        $complemento = new UndefinedComplemento('');
        $this->assertTrue($complemento->isUndefined());
    }
}
