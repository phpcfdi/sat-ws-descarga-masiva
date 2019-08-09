<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\WebClient;

interface WebClientInterface
{
    /**
     * @param Request $request
     * @return Response
     * @throws Exceptions\HttpClientError when a 400 error is returned by server
     * @throws Exceptions\HttpServerError when a 500 error is returned by server or there is a connection issue
     */
    public function call(Request $request): Response;

    public function fireRequest(Request $request): void;

    public function fireResponse(Response $response): void;
}
