<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\WebClient;

use JsonSerializable;

class Request implements JsonSerializable
{
    /** @var string */
    private $method;

    /** @var string */
    private $uri;

    /** @var string */
    private $body;

    /** @var array<string, string> */
    private $headers;

    public function __construct(string $method, string $uri, string $body, array $headers)
    {
        $this->method = $method;
        $this->uri = $uri;
        $this->body = $body;
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

    public function defaultHeaders(): array
    {
        return [
            'Content-type' => 'text/xml; charset="utf-8"',
            'Accept' => 'text/xml',
            'Cache-Control' => 'no-cache',
        ];
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
