<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Internal;

use PhpCfdi\SatWsDescargaMasiva\Shared\Token;
use PhpCfdi\SatWsDescargaMasiva\WebClient\Exceptions\HttpClientError;
use PhpCfdi\SatWsDescargaMasiva\WebClient\Exceptions\HttpServerError;
use PhpCfdi\SatWsDescargaMasiva\WebClient\Exceptions\SoapFaultError;
use PhpCfdi\SatWsDescargaMasiva\WebClient\Exceptions\WebClientException;
use PhpCfdi\SatWsDescargaMasiva\WebClient\Request;
use PhpCfdi\SatWsDescargaMasiva\WebClient\Response;
use PhpCfdi\SatWsDescargaMasiva\WebClient\WebClientInterface;

/**
 * Method Service::consume extraction
 *
 * This class is internal, do not use it outside this project
 * @internal
 */
class ServiceConsumer
{
    public static function consume(WebClientInterface $webclient, string $soapAction, string $uri, string $body, ?Token $token = null): string
    {
        return (new self())->execute($webclient, $soapAction, $uri, $body, $token);
    }

    public function execute(WebClientInterface $webclient, string $soapAction, string $uri, string $body, ?Token $token = null): string
    {
        $headers = $this->createHeaders($soapAction, $token);
        $request = $this->createRequest($uri, $body, $headers);
        $response = $this->runRequest($webclient, $request);
        $this->checkErrors($request, $response);
        return $response->getBody();
    }

    /**
     * @param string $uri
     * @param string $body
     * @param array<string, string> $headers
     * @return Request
     */
    public function createRequest(string $uri, string $body, array $headers): Request
    {
        return new Request('POST', $uri, $body, $headers);
    }

    /**
     * @param string $soapAction
     * @param Token|null $token
     * @return array<string, string>
     */
    public function createHeaders(string $soapAction, ?Token $token = null): array
    {
        $headers = ['SOAPAction' => $soapAction];
        if (null !== $token) {
            $headers['Authorization'] = 'WRAP access_token="' . $token->getValue() . '"';
        }
        return $headers;
    }

    public function runRequest(WebClientInterface $webclient, Request $request): Response
    {
        $webclient->fireRequest($request);
        try {
            $response = $webclient->call($request);
        } catch (WebClientException $exception) {
            $response = $exception->getResponse();
        }
        $webclient->fireResponse($response);
        return $response;
    }

    public function checkErrors(Request $request, Response $response): void
    {
        // evaluate SoapFaultInfo
        $fault = SoapFaultInfoExtractor::extract($response->getBody());
        if (null !== $fault) {
            throw new SoapFaultError($request, $response, $fault);
        }

        // evaluate response
        if ($response->statusCodeIsClientError()) {
            $message = sprintf('Unexpected client error status code %d', $response->getStatusCode());
            throw new HttpClientError($message, $request, $response);
        }
        if ($response->statusCodeIsServerError()) {
            $message = sprintf('Unexpected server error status code %d', $response->getStatusCode());
            throw new HttpServerError($message, $request, $response);
        }
        if ($response->isEmpty()) {
            throw new HttpServerError('Unexpected empty response from server', $request, $response);
        }
    }
}
