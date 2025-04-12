<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\WebClient;

use JsonSerializable;

final class Response implements JsonSerializable
{
    /**
     * Minimal representation of http response object.
     *
     * @param array<string, string> $headers
     */
    public function __construct(private readonly int $statusCode, private readonly string $body, private readonly array $headers = [])
    {
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
        return '' === $this->getBody();
    }

    public function statusCodeIsClientError(): bool
    {
        return $this->statusCode < 500 && $this->statusCode >= 400;
    }

    public function statusCodeIsServerError(): bool
    {
        return $this->statusCode < 600 && $this->statusCode >= 500;
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
