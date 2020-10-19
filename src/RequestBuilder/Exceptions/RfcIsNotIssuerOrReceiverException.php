<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\RequestBuilder\Exceptions;

use LogicException;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\RequestBuilderException;

final class RfcIsNotIssuerOrReceiverException extends LogicException implements RequestBuilderException
{
    /** @var string */
    private $rfcSigner;

    /** @var string */
    private $rfcIssuer;

    /** @var string */
    private $rfcReceiver;

    public function __construct(string $rfcSigner, string $rfcIssuer, string $rfcReceiver)
    {
        $message = sprintf('The RFC "%s" must be the issuer "%s" or receiver "%s"', $rfcSigner, $rfcIssuer, $rfcReceiver);
        parent::__construct($message);
        $this->rfcSigner = $rfcSigner;
        $this->rfcIssuer = $rfcIssuer;
        $this->rfcReceiver = $rfcReceiver;
    }

    public function getRfcSigner(): string
    {
        return $this->rfcSigner;
    }

    public function getRfcIssuer(): string
    {
        return $this->rfcIssuer;
    }

    public function getRfcReceiver(): string
    {
        return $this->rfcReceiver;
    }
}
