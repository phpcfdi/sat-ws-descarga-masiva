<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\WebClient;

class Request
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
        $this->headers = array_filter(array_merge($this->defaultHeaders(), $headers));
    }

    /** @return string */
    public function getMethod(): string
    {
        return $this->method;
    }

    /** @return string */
    public function getUri(): string
    {
        return $this->uri;
    }

    /** @return string */
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
}
