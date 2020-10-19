<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\RequestBuilder\Exceptions;

use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\Exceptions\PeriodStartInvalidDateFormatException;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\RequestBuilderException;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

final class PeriodStartInvalidDateFormatExceptionTest extends TestCase
{
    public function testExceptionInstanceOfRequestBuilderException(): void
    {
        $interfaces = class_implements(PeriodStartInvalidDateFormatException::class) ?: [];
        $this->assertContains(RequestBuilderException::class, $interfaces);
    }

    public function testGetProperties(): void
    {
        $periodStart = 'foo';
        $exception = new PeriodStartInvalidDateFormatException($periodStart);
        $this->assertSame('The start date time "foo" does not have the correct format', $exception->getMessage());
        $this->assertSame($periodStart, $exception->getPeriodStart());
    }
}
