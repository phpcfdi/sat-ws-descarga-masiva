<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\WebClient\Exceptions;

use PhpCfdi\SatWsDescargaMasiva\WebClient\Request;
use PhpCfdi\SatWsDescargaMasiva\WebClient\Response;
use RuntimeException;
use Throwable;

class WebClientException extends RuntimeException
{
    /** @var Request */
    private $request;

    /** @var Response */
    private $response;

    public function __construct(string $message, Request $request, Response $response, Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->request = $request;
        $this->response = $response;
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
