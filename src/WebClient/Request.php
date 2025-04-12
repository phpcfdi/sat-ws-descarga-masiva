<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\WebClient;

use JsonSerializable;

final class Request implements JsonSerializable
{
    /** @var array<string, string> */
    private readonly array $headers;

    /**
     * Minimal representation of http request object.
     *
     * @param array<string, string> $headers
     */
    public function __construct(private readonly string $method, private readonly string $uri, private readonly string $body, array $headers)
    {
        /** @var array<string, string> $headers */
        $headers = array_filter(array_merge($this->defaultHeaders(), $headers));
        $this->headers = $headers;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUri(): string
    {
        return $this->uri;
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

    /**
     * Default headers used on every request
     *
     * @return array<string, string>
     */
    public function defaultHeaders(): array
    {
        return [
            'Content-type' => 'text/xml; charset="utf-8"',
            'Accept' => 'text/xml',
            'Cache-Control' => 'no-cache',
        ];
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
