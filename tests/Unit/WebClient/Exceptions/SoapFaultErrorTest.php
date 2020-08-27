<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\WebClient\Exceptions;

use Exception;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;
use PhpCfdi\SatWsDescargaMasiva\WebClient\Exceptions\HttpClientError;
use PhpCfdi\SatWsDescargaMasiva\WebClient\Exceptions\SoapFaultError;
use PhpCfdi\SatWsDescargaMasiva\WebClient\Request;
use PhpCfdi\SatWsDescargaMasiva\WebClient\Response;
use PhpCfdi\SatWsDescargaMasiva\WebClient\SoapFaultInfo;

class SoapFaultErrorTest extends TestCase
{
    public function testProperties(): void
    {
        $request = new Request('GET', 'unknown://invalid uri/', '', []);
        $response = new Response(200, '', []);
        $fault = new SoapFaultInfo('x-code', 'x-message');
        $previous = new Exception();
        $exception = new SoapFaultError($request, $response, $fault, $previous);
        $this->assertInstanceOf(HttpClientError::class, $exception);
        $this->assertSame($fault, $exception->getFault());
    }
}
