<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\WebClient;

use Closure;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use PhpCfdi\SatWsDescargaMasiva\WebClient\Exceptions\WebClientException;
use Psr\Http\Message\ResponseInterface;

/**
 * GuzzleWebClient is an implementation of WebClientInterface based on guzzlehttp/guzzle:^7.0
 * You can use this class and insert two closures to track request and response
 * before and after call.
 */
class GuzzleWebClient implements WebClientInterface
{
    private readonly GuzzleClient $client;

    /**
     * @param GuzzleClient|null $client If NULL will create an empty Guzzle Client object
     * @param Closure(Request $request):void|null $onFireRequest Called before make the http call
     * @param Closure(Response $response):void|null $onFireResponse Called after make the http call
     */
    public function __construct(
        ?GuzzleClient $client = null,
        private readonly ?Closure $onFireRequest = null,
        private readonly ?Closure $onFireResponse = null,
    ) {
        $this->client = $client ?? new GuzzleClient();
    }

    public function fireRequest(Request $request): void
    {
        if (null !== $this->onFireRequest) {
            call_user_func($this->onFireRequest, $request);
        }
    }

    public function fireResponse(Response $response): void
    {
        if (null !== $this->onFireResponse) {
            call_user_func($this->onFireResponse, $response);
        }
    }

    public function call(Request $request): Response
    {
        try {
            $psr7Response = $this->client->request($request->getMethod(), $request->getUri(), [
                'headers' => $request->getHeaders(),
                'body' => $request->getBody(),
            ]);
        } catch (GuzzleException $exception) {
            $psr7Response = ($exception instanceof RequestException) ? $exception->getResponse() : null;
            $response = $this->createResponseFromPsr7Response($psr7Response);
            $message = sprintf('Error connecting to %s', $request->getUri());
            throw new WebClientException($message, $request, $response, $exception);
        }
        return $this->createResponseFromPsr7Response($psr7Response);
    }

    private function createResponseFromPsr7Response(?ResponseInterface $response): Response
    {
        if (null === $response) {
            return new Response(500, '', []);
        }
        $body = strval($response->getBody());
        $headers = [];
        foreach (array_keys($response->getHeaders()) as $header) {
            $header = strval($header);
            $headers[$header] = $response->getHeaderLine($header);
        }
        return new Response($response->getStatusCode(), $body, $headers);
    }
}
