<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\RequestBuilder\Exceptions;

use LogicException;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\RequestBuilderException;

final class RfcIssuerAndReceiverAreEmptyException extends LogicException implements RequestBuilderException
{
    public function __construct()
    {
        parent::__construct('The RFC issuer and RFC receiver are empty');
    }
}
