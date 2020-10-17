<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\RequestBuilder\Exceptions;

use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\Exceptions\RfcIssuerAndReceiverAreEmptyException;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\RequestBuilderException;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

final class RfcIssuerAndReceiverAreEmptyExceptionTest extends TestCase
{
    public function testExceptionInstanceOfRequestBuilderException(): void
    {
        $interfaces = class_implements(RfcIssuerAndReceiverAreEmptyException::class) ?: [];
        $this->assertContains(RequestBuilderException::class, $interfaces);
    }

    public function testGetProperties(): void
    {
        $exception = new RfcIssuerAndReceiverAreEmptyException();
        $this->assertSame('The RFC issuer and RFC receiver are empty', $exception->getMessage());
    }
}
