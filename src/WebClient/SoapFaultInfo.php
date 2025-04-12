<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\WebClient;

use JsonSerializable;
use Stringable;

final class SoapFaultInfo implements JsonSerializable, Stringable
{
    public function __construct(private readonly string $code, private readonly string $message)
    {
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
