<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\WebClient;

use JsonSerializable;

class Response implements JsonSerializable
{
    /** @var int */
    private $statusCode;

    /** @var string */
    private $body;

    /** @var array */
    private $headers;

    public function __construct(int $statusCode, string $body, array $headers = [])
    {
        $this->statusCode = $statusCode;
        $this->body = $body;
        $this->headers = $headers;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    /** @return array<string, string> */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function isEmpty(): bool
    {
        return ('' === $this->getBody());
    }

    public function statusCodeIsClientError(): bool
    {
        return ($this->statusCode < 500 && $this->statusCode >= 400);
    }

    public function statusCodeIsServerError(): bool
    {
        return ($this->statusCode < 600 && $this->statusCode >= 500);
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
