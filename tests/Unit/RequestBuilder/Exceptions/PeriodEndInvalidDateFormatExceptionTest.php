<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\RequestBuilder\Exceptions;

use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\Exceptions\PeriodEndInvalidDateFormatException;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\RequestBuilderException;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

final class PeriodEndInvalidDateFormatExceptionTest extends TestCase
{
    public function testExceptionInstanceOfRequestBuilderException(): void
    {
        $interfaces = class_implements(PeriodEndInvalidDateFormatException::class) ?: [];
        $this->assertContains(RequestBuilderException::class, $interfaces);
    }

    public function testGetProperties(): void
    {
        $periodEnd = 'foo';
        $exception = new PeriodEndInvalidDateFormatException($periodEnd);
        $this->assertSame('The end date time "foo" does not have the correct format', $exception->getMessage());
        $this->assertSame($periodEnd, $exception->getPeriodEnd());
    }
}
