<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Services\Query;

class QueryResult
{
    /**
     * @var string
     */
    private $requestId;

    /**
     * @var int
     */
    private $statusCode;

    /**
     * @var string
     */
    private $message;

    /**
     * QueryResult constructor.
     *
     * @param string $requestId
     * @param int    $statusCode
     * @param string $message
     */
    public function __construct(string $requestId, int $statusCode, string $message)
    {
        $this->requestId = $requestId;
        $this->statusCode = $statusCode;
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getRequestId(): string
    {
        return $this->requestId;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return bool
     */
    public function isAccepted(): bool
    {
        return 5000 === $this->getStatusCode();
    }
}
