<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\RequestBuilder\Exceptions;

use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\Exceptions\RfcIsNotIssuerOrReceiverException;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\RequestBuilderException;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

final class RfcIsNotIssuerOrReceiverExceptionTest extends TestCase
{
    public function testExceptionInstanceOfRequestBuilderException(): void
    {
        $interfaces = class_implements(RfcIsNotIssuerOrReceiverException::class) ?: [];
        $this->assertContains(RequestBuilderException::class, $interfaces);
    }

    public function testGetProperties(): void
    {
        $rfcSigner = 'a';
        $rfcIssuer = 'b';
        $rfcReceiver = 'c';
        $exception = new RfcIsNotIssuerOrReceiverException($rfcSigner, $rfcIssuer, $rfcReceiver);
        $this->assertSame('The RFC "a" must be the issuer "b" or receiver "c"', $exception->getMessage());
        $this->assertSame($rfcSigner, $exception->getRfcSigner());
        $this->assertSame($rfcIssuer, $exception->getRfcIssuer());
        $this->assertSame($rfcReceiver, $exception->getRfcReceiver());
    }
}
