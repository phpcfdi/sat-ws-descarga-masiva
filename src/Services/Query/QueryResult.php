<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Services\Query;

use PhpCfdi\SatWsDescargaMasiva\Shared\StatusCode;

final class QueryResult
{
    /** @var StatusCode */
    private $status;

    /** @var string */
    private $requestId;

    public function __construct(StatusCode $statusCode, string $requestId)
    {
        $this->status = $statusCode;
        $this->requestId = $requestId;
    }

    /**
     * Status of the verification call
     *
     * @return StatusCode
     */
    public function getStatus(): StatusCode
    {
        return $this->status;
    }

    /**
     * If accepted, contains the request identification required for verification
     *
     * @return string
     */
    public function getRequestId(): string
    {
        return $this->requestId;
    }
}
