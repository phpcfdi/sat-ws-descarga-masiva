<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\WebClient;

/**
 * Interface to proxy an http client
 * @see GuzzleWebClient
 */
interface WebClientInterface
{
    /**
     * Make the Http call to the web service
     * This method should *not* call fireRequest/fireResponse
     *
     * @throws Exceptions\WebClientException when an error is found
     */
    public function call(Request $request): Response;

    /**
     * Method called before calling the web service
     */
    public function fireRequest(Request $request): void;

    /**
     * Method called after calling the web service
     */
    public function fireResponse(Response $response): void;
}
