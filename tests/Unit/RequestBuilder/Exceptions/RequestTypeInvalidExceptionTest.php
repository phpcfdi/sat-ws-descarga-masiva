<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\RequestBuilder\Exceptions;

use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\Exceptions\RequestTypeInvalidException;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\RequestBuilderException;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

final class RequestTypeInvalidExceptionTest extends TestCase
{
    public function testExceptionInstanceOfRequestBuilderException(): void
    {
        $interfaces = class_implements(RequestTypeInvalidException::class) ?: [];
        $this->assertContains(RequestBuilderException::class, $interfaces);
    }

    public function testGetProperties(): void
    {
        $requestType = 'foo';
        $exception = new RequestTypeInvalidException($requestType);
        $this->assertSame('The request type "foo" is not CFDI or Metadata', $exception->getMessage());
        $this->assertSame($requestType, $exception->getRequestType());
    }
}
