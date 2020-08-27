<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\WebClient\Exceptions;

use Exception;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;
use PhpCfdi\SatWsDescargaMasiva\WebClient\Exceptions\WebClientException;
use PhpCfdi\SatWsDescargaMasiva\WebClient\Request;
use PhpCfdi\SatWsDescargaMasiva\WebClient\Response;

class WebClientExceptionTest extends TestCase
{
    public function testProperties(): void
    {
        $message = 'message';
        $request = new Request('GET', 'unknown://invalid uri/', '', []);
        $response = new Response(200, '', []);
        $previous = new Exception();
        $exception = new WebClientException($message, $request, $response, $previous);
        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($request, $exception->getRequest());
        $this->assertSame($response, $exception->getResponse());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
