<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Services\Query;

class QueryResult
{
    /** @var string */
    private $requestId;

    /** @var int */
    private $statusCode;

    /** @var string */
    private $message;

    public function __construct(string $requestId, int $statusCode, string $message)
    {
        $this->requestId = $requestId;
        $this->statusCode = $statusCode;
        $this->message = $message;
    }

    public function getRequestId(): string
    {
        return $this->requestId;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function isAccepted(): bool
    {
        return (5000 === $this->getStatusCode());
    }
}
