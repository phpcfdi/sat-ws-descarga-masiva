<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\RequestBuilder\Exceptions;

use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\Exceptions\PeriodStartGreaterThanEndException;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\RequestBuilderException;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

final class PeriodStartGreaterThanEndExceptionTest extends TestCase
{
    public function testExceptionInstanceOfRequestBuilderException(): void
    {
        $interfaces = class_implements(PeriodStartGreaterThanEndException::class) ?: [];
        $this->assertContains(RequestBuilderException::class, $interfaces);
    }

    public function testGetProperties(): void
    {
        $periodStart = 'foo';
        $periodEnd = 'bar';
        $exception = new PeriodStartGreaterThanEndException($periodStart, $periodEnd);
        $this->assertSame('The period start "foo" is greater than end "bar"', $exception->getMessage());
        $this->assertSame($periodStart, $exception->getPeriodStart());
        $this->assertSame($periodEnd, $exception->getPeriodEnd());
    }
}
