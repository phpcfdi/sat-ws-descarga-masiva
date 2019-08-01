<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use PhpCfdi\SatWsDescargaMasiva\WebClient\Exceptions\HttpServerError;
use PhpCfdi\SatWsDescargaMasiva\WebClient\Request;
use PhpCfdi\SatWsDescargaMasiva\WebClient\Response;
use PhpCfdi\SatWsDescargaMasiva\WebClient\WebClientInterface;
use Psr\Http\Message\ResponseInterface;

class GuzzleWebClient implements WebClientInterface
{
    /** @var GuzzleClient */
    private $client;

    public function __construct(GuzzleClient $client = null)
    {
        $this->client = $client ?? new GuzzleClient();
    }

    public function call(Request $request): Response
    {
        try {
            /** @var ResponseInterface $guzzleResponse */
            $guzzleResponse = $this->client->request($request->getMethod(), $request->getUri(), [
                'headers' => $request->getHeaders(),
                'body' => $request->getBody(),
            ]);
        } catch (ClientException | RequestException $exception) {
            $gRequest = $exception->getRequest();
            $gRequest->getBody()->rewind();
            $gResponse = $exception->getResponse();
            $gResponse->getBody()->rewind();
            throw new HttpServerError(sprintf('Error connecting to %s', $request->getUri()), $request, $exception);
        }
        return new Response(
            $guzzleResponse->getStatusCode(),
            $guzzleResponse->getBody()->getContents(),
            $guzzleResponse->getHeaders()
        );
    }
}
