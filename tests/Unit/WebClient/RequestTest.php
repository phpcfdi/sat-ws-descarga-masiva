<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\WebClient;

use JsonSerializable;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;
use PhpCfdi\SatWsDescargaMasiva\WebClient\Request;

final class RequestTest extends TestCase
{
    public function testProperties(): void
    {
        $method = 'POST';
        $uri = 'http://localhost';
        $body = 'this is the body';
        $customHeaders = ['X-Header' => 'content'];

        $request = new Request($method, $uri, $body, $customHeaders);
        $headers = array_merge($request->defaultHeaders(), $customHeaders);

        $this->assertSame($method, $request->getMethod());
        $this->assertSame($uri, $request->getUri());
        $this->assertSame($body, $request->getBody());
        $this->assertSame($headers, $request->getHeaders());
    }

    public function testJson(): void
    {
        $method = 'POST';
        $uri = 'http://localhost';
        $body = 'this is the body';
        $customHeaders = ['X-Header' => 'content'];

        $request = new Request($method, $uri, $body, $customHeaders);

        $this->assertInstanceOf(JsonSerializable::class, $request);
        $expectedFile = $this->filePath('json/webclient-request.json');
        $this->assertJsonStringEqualsJsonFile($expectedFile, json_encode($request) ?: '');
    }
}
