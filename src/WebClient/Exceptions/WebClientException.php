<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\WebClient\Exceptions;

use PhpCfdi\SatWsDescargaMasiva\WebClient\Request;
use RuntimeException;
use Throwable;

class WebClientException extends RuntimeException
{
    /** @var Request */
    private $request;

    public function __construct(string $message, Request $request, Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->request = $request;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }
}
