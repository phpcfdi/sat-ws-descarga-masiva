<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\WebClient;

use JsonSerializable;

final class SoapFaultInfo implements JsonSerializable
{
    /** @var string */
    private $code;

    /** @var string */
    private $message;

    public function __construct(string $code, string $message)
    {
        $this->code = $code;
        $this->message = $message;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function __toString(): string
    {
        return $this->message;
    }

    /** @return array<string, string> */
    public function jsonSerialize(): array
    {
        return [
            'code' => $this->code,
            'message' => $this->message,
        ];
    }
}
