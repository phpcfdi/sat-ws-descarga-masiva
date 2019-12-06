<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\WebClient;

interface WebClientInterface
{
    /**
     * @param Request $request
     * @return Response
     * @throws Exceptions\WebClientException when an error is found
     */
    public function call(Request $request): Response;

    public function fireRequest(Request $request): void;

    public function fireResponse(Response $response): void;
}
