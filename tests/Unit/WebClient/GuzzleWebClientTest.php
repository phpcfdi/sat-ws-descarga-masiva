<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\WebClient;

use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;
use PhpCfdi\SatWsDescargaMasiva\WebClient\Exceptions\WebClientException;
use PhpCfdi\SatWsDescargaMasiva\WebClient\GuzzleWebClient;
use PhpCfdi\SatWsDescargaMasiva\WebClient\Request;
use PhpCfdi\SatWsDescargaMasiva\WebClient\Response;

class GuzzleWebClientTest extends TestCase
{
    public function testCallThrowsWebException(): void
    {
        $request = new Request('GET', 'unknown://invalid uri/', '', []);
        $webClient = new GuzzleWebClient();
        /** @var WebClientException|null $exception */
        $exception = null;
        try {
            $response = $webClient->call($request);
        } catch (WebClientException $catched) {
            $exception = $catched;
        }
        $this->assertFalse(isset($response), '$response should not be defined');
        if (null === $exception) {
            $this->fail('Exception was not catched');
            return;
        }
        $this->assertInstanceOf(WebClientException::class, $exception);
        $this->assertSame($request, $exception->getRequest());
    }

    public function testFireRequest(): void
    {
        $captured = null;
        $observer = function (Request $request) use (&$captured): void {
            $captured = $request;
        };
        $request = new Request('GET', 'unknown://invalid uri/', '', []);
        $webClient = new GuzzleWebClient(null, $observer);
        $webClient->fireRequest($request);
        $this->assertSame($request, $captured);
    }

    public function testFireResponse(): void
    {
        $captured = null;
        $observer = function (Response $request) use (&$captured): void {
            $captured = $request;
        };
        $response = new Response(200, '', []);
        $webClient = new GuzzleWebClient(null, null, $observer);
        $webClient->fireResponse($response);
        $this->assertSame($response, $captured);
    }
}
