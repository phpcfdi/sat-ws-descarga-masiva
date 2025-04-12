<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\WebClient\Exceptions;

use PhpCfdi\SatWsDescargaMasiva\WebClient\Request;
use PhpCfdi\SatWsDescargaMasiva\WebClient\Response;
use RuntimeException;
use Throwable;

class WebClientException extends RuntimeException
{
    public function __construct(string $message, private readonly Request $request, private readonly Response $response, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }
}
