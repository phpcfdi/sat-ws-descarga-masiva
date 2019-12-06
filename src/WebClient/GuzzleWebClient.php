<?php

/** @noinspection PhpUndefinedClassInspection Guzzle has two definitions */

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\WebClient;

use Closure;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use PhpCfdi\SatWsDescargaMasiva\WebClient\Exceptions\WebClientException;
use Psr\Http\Message\ResponseInterface;

class GuzzleWebClient implements WebClientInterface
{
    /** @var GuzzleClient */
    private $client;

    /** @var Closure|null */
    public $fireRequestClousure;

    /** @var Closure|null */
    public $fireResponseClousure;

    /**
     * GuzzleWebClient constructor.
     *
     * @param GuzzleClient|null $client If NULL will create an empty Guzzle Client object
     * @param Closure|null $onFireRequest Called before make the http call
     * @param Closure|null $onFireResponse Called after make the http call
     */
    public function __construct(
        ?GuzzleClient $client = null,
        ?Closure $onFireRequest = null,
        ?Closure $onFireResponse = null
    ) {
        $this->client = $client ?? new GuzzleClient();
        $this->fireRequestClousure = $onFireRequest;
        $this->fireResponseClousure = $onFireResponse;
    }

    public function fireRequest(Request $request): void
    {
        if (null !== $this->fireRequestClousure) {
            call_user_func($this->fireRequestClousure, $request);
        }
    }

    public function fireResponse(Response $response): void
    {
        if (null !== $this->fireResponseClousure) {
            call_user_func($this->fireResponseClousure, $response);
        }
    }

    public function call(Request $request): Response
    {
        try {
            /** @var ResponseInterface $psr7Response */
            $psr7Response = $this->client->request($request->getMethod(), $request->getUri(), [
                'headers' => $request->getHeaders(),
                'body' => $request->getBody(),
            ]);
        } catch (GuzzleException $exception) {
            /** @var ResponseInterface|null $psr7Response */
            $psr7Response = ($exception instanceof RequestException) ? $exception->getResponse() : null;
            $response = $this->createResponseFromPsr7Response($psr7Response);
            $message = sprintf('Error connecting to %s', $request->getUri());
            throw new WebClientException($message, $request, $response, $exception);
        }
        $response = $this->createResponseFromPsr7Response($psr7Response);
        return $response;
    }

    private function createResponseFromPsr7Response(?ResponseInterface $response): Response
    {
        if (null === $response) {
            return new Response(500, '', []);
        }
        $body = strval($response->getBody());
        $headers = [];
        foreach (array_keys($response->getHeaders()) as $header) {
            $headers[$header] = $response->getHeaderLine($header);
        }
        return new Response($response->getStatusCode(), $body, $headers);
    }
}
