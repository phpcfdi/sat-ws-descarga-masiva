<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\Shared;

use PhpCfdi\SatWsDescargaMasiva\Shared\ComplementoInterface;
use PhpCfdi\SatWsDescargaMasiva\Shared\ComplementoUndefined;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

final class ComplementoUndefinedTest extends TestCase
{
    public function testCreateUndefinedByName(): void
    {
        $complemento = ComplementoUndefined::undefined();
        $this->assertInstanceOf(ComplementoInterface::class, $complemento);
        $this->assertTrue($complemento->isUndefined());
    }

    public function testCreateUndefinedByMethod(): void
    {
        $complemento = new ComplementoUndefined('');
        $this->assertTrue($complemento->isUndefined());
    }
}
