<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\Internal;

use PhpCfdi\SatWsDescargaMasiva\Internal\ServiceConsumer;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTime;
use PhpCfdi\SatWsDescargaMasiva\Shared\Token;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;
use PhpCfdi\SatWsDescargaMasiva\WebClient\Exceptions\HttpClientError;
use PhpCfdi\SatWsDescargaMasiva\WebClient\Exceptions\HttpServerError;
use PhpCfdi\SatWsDescargaMasiva\WebClient\Exceptions\SoapFaultError;
use PhpCfdi\SatWsDescargaMasiva\WebClient\Exceptions\WebClientException;
use PhpCfdi\SatWsDescargaMasiva\WebClient\Request;
use PhpCfdi\SatWsDescargaMasiva\WebClient\Response;
use PhpCfdi\SatWsDescargaMasiva\WebClient\WebClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

/**
 * @covers \PhpCfdi\SatWsDescargaMasiva\Internal\ServiceConsumer
 */
class ServiceConsumerTest extends TestCase
{
    public function testExecute(): void
    {
        // the response is valid and does not contain any error
        $responseBody = $this->fileContents('authenticate/response-with-token.xml');
        $response = new Response(200, $responseBody);

        /** @var WebClientInterface&MockObject $webClient */
        $webClient = $this->getMockBuilder(WebClientInterface::class)->getMock();
        $webClient->expects($this->once())->method('call')->willReturn($response);

        $consumer = new ServiceConsumer();
        $token = new Token(new DateTime('2020-01-13 14:15:16'), new DateTime('2020-01-13 14:15:16'), 'token-value');
        $return = $consumer->execute($webClient, 'soap-action', 'uri', 'body', $token);

        $this->assertSame($responseBody, $return);
    }

    public function testExecuteWithError(): void
    {
        // the response is valid and does not contain any error
        $response = new Response(500, 'Internal Server Error for Testing.');
        $request = new Request('POST', 'uri', 'request', []);

        /** @var WebClientInterface&MockObject $webClient */
        $webClient = $this->getMockBuilder(WebClientInterface::class)->getMock();
        $exception = new WebClientException('Testing exception', $request, $response);
        $webClient->expects($this->once())->method('call')->willThrowException($exception);

        $consumer = new ServiceConsumer();
        $token = new Token(new DateTime('2020-01-13 14:15:16'), new DateTime('2020-01-13 14:15:16'), 'token-value');

        $catchedException = null;
        try {
            $consumer->execute($webClient, 'soap-action', 'uri', 'body', $token);
        } catch (Throwable $thrownException) {
            $catchedException = $thrownException;
        }
        $this->assertInstanceOf(HttpServerError::class, $catchedException);
        $this->assertSame($catchedException->getPrevious(), $exception);
    }

    public function testCreateRequest(): void
    {
        $consumer = new ServiceConsumer();
        $request = $consumer->createRequest('uri', 'body', ['x-foo' => 'foo value']);
        $expected = new Request('POST', 'uri', 'body', ['x-foo' => 'foo value']);
        $this->assertEquals($expected, $request);
    }

    public function testCreateHeadersWithToken(): void
    {
        $consumer = new ServiceConsumer();
        $soapAction = 'soap-action';
        $tokenValue = 'token-value';
        $token = new Token(new DateTime('2020-01-13 14:15:16'), new DateTime('2020-01-13 14:15:16'), $tokenValue);
        $headers = $consumer->createHeaders($soapAction, $token);
        $expected = [
            'SOAPAction' => $soapAction,
            'Authorization' => 'WRAP access_token="' . $tokenValue . '"',
        ];
        $this->assertSame($expected, $headers);
    }

    public function testCreateHeadersWithOutToken(): void
    {
        $consumer = new ServiceConsumer();
        $soapAction = 'soap-action';
        $headers = $consumer->createHeaders($soapAction, null);
        $expected = ['SOAPAction' => $soapAction];
        $this->assertSame($expected, $headers);
    }

    public function testRunRequest(): void
    {
        $request = new Request('POST', 'uri', 'request', ['x-foo' => 'foo value']);
        $response = new Response(200, 'response');

        /** @var WebClientInterface&MockObject $webClient */
        $webClient = $this->getMockBuilder(WebClientInterface::class)->getMock();
        $webClient->expects($this->once())->method('fireRequest')->with($request);
        $webClient->expects($this->once())->method('call')->with($request)->willReturn($response);
        $webClient->expects($this->once())->method('fireResponse')->with($response);

        $consumer = new ServiceConsumer();
        $return = $consumer->runRequest($webClient, $request);

        $this->assertSame($response, $return);
    }

    public function testRunRequestWithWebClientException(): void
    {
        $request = new Request('POST', 'uri', 'request', ['x-foo' => 'foo value']);
        $response = new Response(500, '');
        $exception = new WebClientException('foo', $request, $response);

        /** @var WebClientInterface&MockObject $webClient */
        $webClient = $this->getMockBuilder(WebClientInterface::class)->getMock();
        $webClient->expects($this->once())->method('fireRequest')->with($request);
        $webClient->expects($this->once())->method('call')->willThrowException($exception);
        $webClient->expects($this->once())->method('fireResponse')->with($response);

        $consumer = new ServiceConsumer();
        // use try catch and not expectException because must check WebClientException contains expected response
        try {
            $consumer->runRequest($webClient, $request);
        } catch (WebClientException $webClientException) {
            $this->assertSame($response, $webClientException->getResponse());
            return;
        }
        $this->fail('The WebClientException was not thrown');
    }

    public function testCheckErrorWithFault(): void
    {
        $request = new Request('POST', 'uri', 'body', []);
        $responseBody = $this->fileContents('authenticate/response-with-error.xml');
        $response = new Response(200, $responseBody);
        $consumer = new ServiceConsumer();

        $this->expectException(SoapFaultError::class);
        $consumer->checkErrors($request, $response);
    }

    public function testCheckErrorOnClientSide(): void
    {
        $request = new Request('POST', 'uri', 'body', []);
        $response = new Response(400, '<xml/>');
        $consumer = new ServiceConsumer();

        $this->expectException(HttpClientError::class);
        $this->expectExceptionMessage('Unexpected client error status code');
        $consumer->checkErrors($request, $response);
    }

    public function testCheckErrorOnServerSide(): void
    {
        $request = new Request('POST', 'uri', 'body', []);
        $response = new Response(500, '<xml/>');
        $consumer = new ServiceConsumer();

        $this->expectException(HttpServerError::class);
        $this->expectExceptionMessage('Unexpected server error status code');
        $consumer->checkErrors($request, $response);
    }

    public function testCheckErrorOnEmptyResponse(): void
    {
        $request = new Request('POST', 'uri', 'body', []);
        $response = new Response(200, '');
        $consumer = new ServiceConsumer();

        $this->expectException(HttpServerError::class);
        $this->expectExceptionMessage('Unexpected empty response from server');
        $consumer->checkErrors($request, $response);
    }
}
