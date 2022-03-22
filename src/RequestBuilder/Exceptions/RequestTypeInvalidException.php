<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\RequestBuilder\Exceptions;

use LogicException;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\RequestBuilderException;

final class RequestTypeInvalidException extends LogicException implements RequestBuilderException
{
    /** @var string */
    private $requestType;

    public function __construct(string $requestType)
    {
        parent::__construct(sprintf('The request type "%s" is not CFDI, Retencion or Metadata', $requestType));
        $this->requestType = $requestType;
    }

    public function getRequestType(): string
    {
        return $this->requestType;
    }
}
