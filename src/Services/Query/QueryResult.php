<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Services\Query;

use PhpCfdi\SatWsDescargaMasiva\Shared\StatusCode;

class QueryResult
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

    public function getStatus(): StatusCode
    {
        return $this->status;
    }

    public function getRequestId(): string
    {
        return $this->requestId;
    }
}
