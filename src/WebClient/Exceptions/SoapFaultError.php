<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\WebClient\Exceptions;

use PhpCfdi\SatWsDescargaMasiva\WebClient\Request;
use PhpCfdi\SatWsDescargaMasiva\WebClient\Response;
use PhpCfdi\SatWsDescargaMasiva\WebClient\SoapFaultInfo;
use Throwable;

class SoapFaultError extends HttpClientError
{
    private readonly SoapFaultInfo $fault;

    public function __construct(Request $request, Response $response, SoapFaultInfo $fault, ?Throwable $previous = null)
    {
        $message = sprintf('Fault: %s - %s', $fault->getCode(), $fault->getMessage());
        parent::__construct($message, $request, $response, $previous);
        $this->fault = $fault;
    }

    public function getFault(): SoapFaultInfo
    {
        return $this->fault;
    }
}
