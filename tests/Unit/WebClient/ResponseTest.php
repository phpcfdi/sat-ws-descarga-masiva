<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\WebClient;

use JsonSerializable;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;
use PhpCfdi\SatWsDescargaMasiva\WebClient\Response;

final class ResponseTest extends TestCase
{
    public function testProperties(): void
    {
        $statusCode = 200;
        $body = 'this is the body';
        $headers = ['X-Header' => 'content'];
        $response = new Response($statusCode, $body, $headers);
        $this->assertSame($statusCode, $response->getStatusCode());
        $this->assertSame($body, $response->getBody());
        $this->assertSame($headers, $response->getHeaders());
        $this->assertFalse($response->isEmpty());
    }

    public function testResponseWithEmptyContent(): void
    {
        $response = new Response(200, '', []);
        $this->assertEmpty($response->getBody());
        $this->assertTrue($response->isEmpty());
    }

    /**
     * @testWith [200, false]
     *           [399, false]
     *           [400, true]
     *           [499, true]
     *           [500, false]
     */
    public function testStatusCodeIsClientError(int $code, bool $expected): void
    {
        $response = new Response($code, '', []);
        $this->assertSame($expected, $response->statusCodeIsClientError());
    }

    /**
     * @testWith [200, false]
     *           [399, false]
     *           [400, false]
     *           [499, false]
     *           [500, true]
     *           [599, true]
     *           [600, false]
     */
    public function testStatusCodeIsServerError(int $code, bool $expected): void
    {
        $response = new Response($code, '', []);
        $this->assertSame($expected, $response->statusCodeIsServerError());
    }

    public function testJson(): void
    {
        $statusCode = 200;
        $body = 'this is the body';
        $headers = [
            'X-First' => 'first header',
            'X-Second' => 'second header',
        ];
        $response = new Response($statusCode, $body, $headers);
        $this->assertInstanceOf(JsonSerializable::class, $response);
        $expectedFile = $this->filePath('json/webclient-response.json');
        $this->assertJsonStringEqualsJsonFile($expectedFile, json_encode($response) ?: '');
    }
}
