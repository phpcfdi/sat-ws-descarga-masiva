<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\WebClient\Exceptions;

use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;
use PhpCfdi\SatWsDescargaMasiva\WebClient\Exceptions\HttpClientError;
use PhpCfdi\SatWsDescargaMasiva\WebClient\Exceptions\WebClientException;
use PhpCfdi\SatWsDescargaMasiva\WebClient\Request;
use PhpCfdi\SatWsDescargaMasiva\WebClient\Response;

class HttpClientErrorTest extends TestCase
{
    public function testInstanceOfWebClientException(): void
    {
        $exception = new HttpClientError(
            'message',
            new Request('GET', 'unknown://invalid uri/', '', []),
            new Response(200, '', [])
        );
        $this->assertInstanceOf(WebClientException::class, $exception);
    }
}
