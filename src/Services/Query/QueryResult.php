<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Services\Query;

use JsonSerializable;
use PhpCfdi\SatWsDescargaMasiva\Shared\StatusCode;

final class QueryResult implements JsonSerializable
{
    public function __construct(private readonly StatusCode $status, private readonly string $requestId)
    {
    }

    /**
     * Status of the verification call
     */
    public function getStatus(): StatusCode
    {
        return $this->status;
    }

    /**
     * If accepted, contains the request identification required for verification
     */
    public function getRequestId(): string
    {
        return $this->requestId;
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return [
            'status' => $this->status,
            'requestId' => $this->requestId,
        ];
    }
}
